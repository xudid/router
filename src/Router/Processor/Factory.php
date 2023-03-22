<?php

namespace Router\Processor;

use Router\Route;

class Factory
{
    public static function create(Route $route, $resultKey = ''): ProcessorInterface
    {
        $callable = $route->getCallable();
        if (!$callable) {
            return new NullProcessor();
        }

        $processor = null;
        switch ($route->getCallableType()) {
            case 'action':
                $processor = new Action($callable, $route->getValues());
                break;
            case 'controller':
                $processor = new Controller($callable, $route->getValues());
                break;
            case 'callback':
                $processor = new Callback($callable, $route->getValues());
                break;
            default:
                $processor = new NullProcessor();
        }

        if ($resultKey) {
            $processor = $processor->withResultKey($resultKey);
        }

        return $processor;
    }
}
