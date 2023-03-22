<?php


use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Illusion\Illusion;
use Psr\Http\Message\ResponseInterface;
use Router\Dispatcher;
use PHPUnit\Framework\TestCase;
use Router\Processor\Factory;
use Router\Route;

class DispatcherTest extends TestCase
{
    private function makeProcessor($route = null)
    {
        $builder = $this->getMockBuilder(Route::class);
        if (!$route) {
            $route = $builder->getMock();
            $route->method('getCallable')->willReturn(fn() => '');
            $route->method('getCallableType')->willReturn('callback');
        }

        return Factory::create($route);
    }

    private function makeRequestHandler($content = '')
    {
        $builder = $this->getMockBuilder(\Psr\Http\Server\RequestHandlerInterface::class);
        $mock = $builder->getMock();
        $mock->method('handle')->willReturn(new Response(200, [], $content));

        return $mock;
    }
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
