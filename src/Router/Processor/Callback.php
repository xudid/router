<?php

namespace Router\Processor;

use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class Callback implements ProcessorInterface
{

    private Response $response;
    private $callable;
    private array $params = [];

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function setParams(array $params): ProcessorInterface
    {
        $this->params = $params;
        return $this;
    }

    public function execute(): ResponseInterface
    {
        if (!$this->callable) {
            throw new Exception('Processor: need a callable to process');
        }
        $result = call_user_func_array($this->callable, [$this->params]);
        $this->response->getBody()->write($result);
        return $this->response;
    }

    public function setCallable($callable): ProcessorInterface
    {
        $this->callable = $callable;
        return $this;
    }
}