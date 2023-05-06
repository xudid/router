<?php
namespace Router\Processor;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Action extends AbstractProcessor
{
    private array $params;
    private $callable;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container, $callable, array $params = [])
    {
        $this->callable = $callable;
        $this->params = $params;
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!class_exists($this->callable)) {
            throw new Exception();
        }

        $action = $this->container->get($this->callable);
        if (!method_exists($action, 'handle')) {
            throw new Exception();
        }

        $result = call_user_func_array([$action, 'handle'], [$this->params]);
        $request = $request->withAttribute($this->resultKey, $result);
        $response = $handler->handle($request);

        return $response;
    }
}
