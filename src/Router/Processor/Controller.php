<?php

namespace Router\Processor;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Xudid\Container\Weaver;

class Controller extends AbstractProcessor
{
    private array $params = [];
    private string $controller;
    private string $method;
    private Weaver $weaver;

    public function __construct(Weaver $weaver, $callable, array $params = [])
    {
        $this->weaver = $weaver;
        [$this->controller, $this->method] = $callable;
        $this->params = $params;
    }


    /**
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!class_exists($this->controller)) {
            throw new Exception();
        }

        $response = $handler->handle($request);

        $constructorArguments = [
            ServerRequestInterface::class => $request,
            ResponseInterface::class => $response,
        ];
        $controller = $this->weaver->make($this->controller, $constructorArguments);
        if (!method_exists($controller, $this->method)) {
            throw new Exception();
        }

        $method = $this->method;
        $result = call_user_func_array([$controller, $method], $this->params);
        $request = $request->withAttribute($this->resultKey, $result);
        $handler->handle($request);

        return $response;
    }
}