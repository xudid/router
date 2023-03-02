<?php

namespace Router\Processor;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Router\Route;

class Factory
{
    public static function create(RequestInterface $request, ResponseInterface $response, Route $route): ProcessorInterface
    {
        $callable = $route->getCallable();
        if (!$callable) {
            throw new Exception();
        }
        $processor = null;
        switch ($route->getCallableType()) {
            case 'action':
                $processor = new Action($response);
                break;
            case 'controller':
                $processor = new Controller($request, $response);
                break;
            case 'callback':
                $processor = new Callback($response);
                break;
        }

        $processor->setCallable($callable);
        $processor->setparams($route->getValues());

        return $processor;
    }
}