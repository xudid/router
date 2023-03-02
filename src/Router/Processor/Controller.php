<?php

namespace Router\Processor;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Controller implements ProcessorInterface
{
    private ResponseInterface $response;
    private array $params = [];
    private string $controller;
    private string $method;
    private RequestInterface $request;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function setParams(array $params): ProcessorInterface
    {
        $this->params = $params;
        return $this;
    }

    public function setCallable($callable): ProcessorInterface
    {
        [$this->controller, $this->method] = $callable;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function execute(): ResponseInterface
    {
        if (class_exists($this->controller)) {
            $controller = new $this->controller($this->request, $this->response);
            $method = $this->method;
            if (method_exists($controller, $this->method)) {
                $result = call_user_func_array([$controller, $method], $this->params);
                return $result;
            } else {
                throw new Exception();
            }
        } else {
            throw new Exception();
        }

        return $this->response;
    }
}