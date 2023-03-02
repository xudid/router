<?php

namespace Router;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Router\Processor\Factory;

class Dispatcher
{
    private ResponseInterface $response;
    private Factory $processorFactory;
    private RequestInterface $request;

    public function __construct(Factory $processorFactory, RequestInterface $request, ResponseInterface $response)
    {
        $this->processorFactory = $processorFactory;
        $this->response = $response;
        $this->request = $request;
    }


    public function handle(?Route $route = null): ResponseInterface
    {
        try {
            if ($route === null) {
                $response = $this->response->withStatus(404);
                return $response;
            }

            $processor = $this->processorFactory::create($this->request, $this->response, $route);
            $response = $processor->execute();

            return  $response;
        } catch (Exception $ex) {
            $response = $this->response->withStatus(500);
            return $response;
        }
    }
}
