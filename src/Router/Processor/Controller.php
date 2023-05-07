<?php

namespace Router\Processor;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Controller extends AbstractProcessor
{
    private array $params = [];
    private string $controller;
    private string $method;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container, $callable, array $params = [])
    {
        $this->container = $container;
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
        // store request and response in container to auto-wire controller with the good messages
        // make ContainerContract
        $this->container->set(ServerRequestInterface::class, $request);
        $this->container->set(ResponseInterface::class, $response);
        $controller = $this->container->get($this->controller);
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