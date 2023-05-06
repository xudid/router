<?php

namespace Test;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Router\Processor\ProcessorInterface;
use Router\Route;
use Router\Processor\Factory as ProcessorFactory;


class Factory extends TestCase
{
    protected ServerRequestInterface $request;

    public function setUp(): void
    {
        $this->request = ServerRequest::fromGlobals();
    }

    protected function makeFactory($className = ''): ProcessorFactory
    {
        $container = $this->makeContainer($className);
        $factory = new ProcessorFactory($container);
        return $factory;
    }

    protected function makeContainer($className)
    {
        $builder = $this->getMockBuilder(ContainerInterface::class);
        $container = $builder->getMock();
        if ($className) {
            $container->method('get')->willReturn(new $className(ServerRequest::fromGlobals(), new Response()));
        }
        return $container;
    }

    protected function makeProcessor($route = null): ProcessorInterface
    {
        $builder = $this->getMockBuilder(Route::class);
        if (!$route) {
            $route = $builder->getMock();
            $route->method('getCallable')->willReturn(fn() => '');
            $route->method('getCallableType')->willReturn('callback');
        }
        $factory = $this->makeFactory();
        return $factory->create($route);
    }

    protected function makeRequestHandler($content = '')
    {
        $builder = $this->getMockBuilder(RequestHandlerInterface::class);
        $mock = $builder->getMock();
        $mock->method('handle')->willReturnCallback(function ($request) use ($mock, $content) {
            $this->request = $request;
            return new Response(200, [], $content);
        });

        return $mock;
    }
}
