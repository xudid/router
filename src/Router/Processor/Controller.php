<?php

namespace Router\Processor;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Controller extends AbstractProcessor
{
    private array $params = [];
    private string $controller;
    private string $method;

    public function __construct($callable, array $params = [])
    {
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
        $controller = new $this->controller($request, $response);
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