<?php

namespace Router\Processor;

use Psr\Container\ContainerInterface;
use Router\Route;

class Factory
{
    private ContainerInterface $container;
    public function __construct(ContainerInterface $container)
    {

        $this->container = $container;
    }

    public function create(Route $route, $resultKey = ''): ProcessorInterface
    {
        $callable = $route->getCallable();
        if (!$callable) {
            return new NullProcessor();
        }

        $processor = match ($route->getCallableType()) {
            'action' => new Action($this->container, $callable, $route->getValues()),
            'controller' => new Controller($this->container, $callable, $route->getValues()),
            'callback' => new Callback($callable, $route->getValues()),
            default => new NullProcessor(),
        };

        if ($resultKey) {
            $processor = $processor->withResultKey($resultKey);
        }

        return $processor;
    }
}
