<?php

namespace Router;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Router\Processor\NullProcessor;
use Router\Processor\ProcessorInterface;

class Dispatcher
{
    private ProcessorInterface $processor;
    private RequestHandlerInterface $handler;
    private RequestInterface $request;
    private ResponseInterface $response;

    public function __construct(ProcessorInterface $processor, RequestInterface $request, RequestHandlerInterface &$handler)
    {
        $this->processor = $processor;
        $this->response = $handler->handle($request);
        $this->request = $request;
        $this->handler = $handler;
    }


    public function dispatch(Route $route): ResponseInterface
    {
        if (is_null($route->getCallable())) {
            $this->response = $this->response->withStatus(404);
            return $this->response;
        }

        if ($this->processor instanceof NullProcessor) {
            $this->response = $this->response->withStatus(500);
            return $this->response;
        }

        return $this->processor->process($this->request, $this->handler);
    }
}
