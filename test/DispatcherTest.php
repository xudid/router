<?php


use GuzzleHttp\Psr7\Response;
use Illusion\Illusion;
use Psr\Http\Message\ResponseInterface;
use Router\Dispatcher;
use PHPUnit\Framework\TestCase;
use Router\Processor\Factory;
use Router\Route;

class DispatcherTest extends TestCase
{
    public function testHandleReturnResponseInterface()
    {
        $response = new Response(200, [], 'hello');
        $dispatcher = new Dispatcher(new Factory(), \GuzzleHttp\Psr7\ServerRequest::fromGlobals(), $response);
        $result = $dispatcher->handle();
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testHandleRouteIsNullReturn404Response()
    {
        $response = new Response(200, [], 'hello');
        $dispatcher = new Dispatcher(new Factory(), \GuzzleHttp\Psr7\ServerRequest::fromGlobals(), $response);
        $result = $dispatcher->handle(null);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testHandleRouteWithNullCallableReturn500Response()
    {
        $response = new Response(200, [], 'hello');
        $dispatcher = new Dispatcher(new Factory(), \GuzzleHttp\Psr7\ServerRequest::fromGlobals(), $response);
        $route = new Route('simple/new', 'simple_new', null);

        $result = $dispatcher->handle($route);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(500, $result->getStatusCode());
    }

    public function testHandleRouteWithControllerAndMethodReturn200Response()
    {
        $response = new Response(200, [], 'hello');
        $dispatcher = new Dispatcher(new Factory(), \GuzzleHttp\Psr7\ServerRequest::fromGlobals(), $response);
        $className = Illusion::withClass('FakeController2')
            ->use('GuzzleHttp\Psr7\Response')
            ->withMethod('test', "return new Response(200, [], 'hello 1, 2, 3');")
            ->project();

        $route = new Route('simple/new', 'simple_new', [$className, 'test']);

        $result = $dispatcher->handle($route);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testHandleRouteWithActionReturn200Response()
    {
        $response = new Response(200, [], 'hello');
        $dispatcher = new Dispatcher(new Factory(), \GuzzleHttp\Psr7\ServerRequest::fromGlobals(), $response);
        $className = Illusion::withClass('Action')
            ->withMethod('handle', "return 'hello 1, 2, 3';")
            ->project();
        $route = new Route('simple/new', 'simple_new', $className);

        $result = $dispatcher->handle($route);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testReturn200ResponseContainsExternalContent()
    {
        $response = new Response(200, [], 'hello');
        $dispatcher = new Dispatcher(new Factory(), \GuzzleHttp\Psr7\ServerRequest::fromGlobals(), $response);
        $route = new Route('simple/new', 'simple_new', fn() => '');

        $result = $dispatcher->handle($route);
        $this->assertStringContainsString('hello', $result->getBody()->getContents());
    }
}
