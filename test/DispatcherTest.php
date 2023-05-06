<?php

namespace Test;

use GuzzleHttp\Psr7\ServerRequest;
use Illusion\Illusion;
use Psr\Http\Message\ResponseInterface;
use Router\Dispatcher;
use Router\Route;
use Test\Factory as TestFactory;

class DispatcherTest extends TestFactory
{
    public function testHandleReturnResponseInterface()
    {
        $processor = $this->makeProcessor();
        $requestHandler = $this->makeRequestHandler();
        $dispatcher = new Dispatcher($processor, ServerRequest::fromGlobals(), $requestHandler);
        $result = $dispatcher->dispatch(new Route());
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testHandleRouteWithNullCallableReturn404Response()
    {
        $route = new Route('simple/new', 'simple_new', null);
        $processor = $this->makeProcessor($route);
        $requestHandler = $this->makeRequestHandler();
        $dispatcher = new Dispatcher($processor, ServerRequest::fromGlobals(), $requestHandler);

        $result = $dispatcher->dispatch($route);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testHandleRouteWithControllerAndMethodReturn200Response()
    {
        $processor = $this->makeProcessor();
        $requestHandler = $this->makeRequestHandler();
        $dispatcher = new Dispatcher($processor, ServerRequest::fromGlobals(), $requestHandler);
        $className = Illusion::withClass('FakeController2')
            ->use('GuzzleHttp\Psr7\Response')
            ->withMethod('test', "return 'hello 1, 2, 3';")
            ->project();

        $route = new Route('simple/new', 'simple_new', [$className, 'test']);

        $result = $dispatcher->dispatch($route);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testHandleRouteWithActionReturn200Response()
    {
        $processor = $this->makeProcessor();
        $requestHandler = $this->makeRequestHandler();
        $dispatcher = new Dispatcher($processor, ServerRequest::fromGlobals(), $requestHandler);
        $className = Illusion::withClass('Action')
            ->withMethod('handle', "return 'hello 1, 2, 3';")
            ->project();
        $route = new Route('simple/new', 'simple_new', $className);

        $result = $dispatcher->dispatch($route);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }
}
