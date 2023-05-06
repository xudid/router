<?php

namespace Test;

use Core\Controller\BaseController;
use Exception;
use GuzzleHttp\Psr7\ServerRequest;
use Illusion\Illusion;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Router\Processor\Callback;
use Router\Processor\Controller;
use Router\Processor\Action;

class CallableProcessorTest extends \Test\Factory
{
    protected ServerRequestInterface $request;

    public function setUp(): void
    {
        $this->request = ServerRequest::fromGlobals();
    }
    public function testExecuteCallbackProcessorWithoutCallbackThrowException()
    {
        $processor = new Callback('');
        $this->expectException(Exception::class);
        $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler());
    }

    public function testCallReturnResponseInterface()
    {
        $processor = new Callback(fn() => '');
        $result = $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler());
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testCallReturnResponseCanContainsExternalContent()
    {
        $processor = new Callback(fn() => '');
        $response = $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler('hello'));
        $this->assertStringContainsString('hello', $response->getBody());
    }

    public function testExecuteExecutesCallable()
    {
        $processor = new Callback(fn() => 'hello');
        $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler());
        $this->assertStringContainsString('hello', $this->request->getAttribute('result'));
        $callback = function ($params) {
            return 'hello ' . implode(', ', $params);
        };

        $processor = new Callback($callback, [1, 2, 3]);
        $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler());
        $this->assertStringContainsString('hello 1, 2, 3', $this->request->getAttribute('result'));

        $className = Illusion::withClass('FakeController')
            ->use('GuzzleHttp\Psr7\Response')
            ->withMethod('test', "return 'hello 1, 2, 3';")
            ->project();

        $container = $this->makeContainer($className);
        $processor = new Controller($container, [$className, 'test'], [1, 2, 3]);
        $this->request = ServerRequest::fromGlobals();
        $processor->process($this->request, $this->makeRequestHandler());
        $this->assertStringContainsString('hello 1, 2, 3', $this->request->getAttribute('result') ?? '');

        $className = Illusion::withClass('FakeController')
            ->use('GuzzleHttp\Psr7\Response')
            ->extends(BaseController::class)
            ->withMethod('test', "return 'hello 1, 2, 3';")
            ->project();

        $container = $this->makeContainer($className);
        $processor = new Controller($container, [$className, 'test'], [1, 2, 3]);
        $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler());
        $this->assertStringContainsString('hello 1, 2, 3', $this->request->getAttribute('result') ?? '');

        $className = Illusion::withClass('FakeAction')
            ->use('GuzzleHttp\Psr7\Response')
            ->withMethod('handle', "return 'hello 1, 2, 3';")
            ->project();
        $container = $this->makeContainer($className);
        $processor = new Action($container,  $className, [1, 2, 3]);
        $processor->process($this->request, $this->makeRequestHandler());
        $this->assertStringContainsString('hello 1, 2, 3', $this->request->getAttribute('result'));
    }
}
