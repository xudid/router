<?php

namespace Router\Processor;

use Router\Route;
use Xudid\Container\Weaver;

class Factory
{
    private Weaver $weaver;
    public function __construct(Weaver $weaver)
    {

        $this->weaver = $weaver;
    }

    public function create(Route $route, $resultKey = ''): ProcessorInterface
    {
        $callable = $route->getCallable();
        if (!$callable) {
            return new NullProcessor();
        }

        $processor = match ($route->getCallableType()) {
            // Action + Controller => ObjectProcessor
            // make a callback who call method on object with params
            // Processor unique who execute callback
            'action' => new Action($this->weaver, $callable, $route->getValues()),
            'controller' => new Controller($this->weaver, $callable, $route->getValues()),
            'callback' => new Callback($callable, $route->getValues()),
            default => new NullProcessor(),
        };

        if ($resultKey) {
            $processor = $processor->withResultKey($resultKey);
        }

        return $processor;
    }
}
