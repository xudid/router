<?php

namespace Router\Processor;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractProcessor implements ProcessorInterface
{
    protected $resultKey = 'result';
    public function withResultKey(string $resultKey)
    {
        if ($resultKey) {
            $this->resultKey = $resultKey;
        }

        return $this;
    }





    public abstract function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;
}