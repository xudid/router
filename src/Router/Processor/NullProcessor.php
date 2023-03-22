<?php

namespace Router\Processor;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NullProcessor implements ProcessorInterface
{

    public function addPreProcessor(Closure $preProcessor)
    {

    }

    public function addPostProcessor(Closure $postProcessor)
    {

    }

    public function preProcess($input)
    {

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }

    public function postProcess(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {

    }
}