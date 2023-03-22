<?php

namespace Router\Processor;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Callback extends AbstractProcessor
{
    private $callable = '';
    private array $params;

    public function __construct($callable, array $params = [])
    {
        $this->callable = $callable;
        $this->params = $params;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->callable) {
            throw new Exception('Processor: need a callable to process');
        }

        $result = call_user_func_array($this->callable, [$this->params]);
        $request = $request->withAttribute($this->resultKey, $result);
        $response = $handler->handle($request);

        return $response;
    }
}
