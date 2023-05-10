<?php

namespace Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Router\Processor\Factory;

/**
 * Class DispatcherMiddleware
 */
class DispatcherMiddleware implements MiddlewareInterface
{
    private ResponseInterface $response;

    private string $resultKey = 'result';
    private string $routeKey = 'route';
    private Factory $processorFactory;

    public function __construct(Factory $factory, string $resultKey = '', string $routeKey = '')
    {
        if ($resultKey) {
            $this->resultKey = $resultKey;
        }

        if ($routeKey) {
            $this->routeKey = $routeKey;
        }

        $this->processorFactory = $factory;
    }

    function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->response = $handler->handle($request);
        $route = $request->getAttribute($this->routeKey);

        $processor = $this->processorFactory->create($route, $this->resultKey);
        $dispatcher = new Dispatcher($processor, $request, $handler);
        $this->response = $dispatcher->dispatch($route);

        return $this->response;
    }
}
