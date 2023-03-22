<?php

use Core\Controller\BaseController;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Illusion\Illusion;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Router\Processor\Callback;
use Router\Processor\Controller;
use Router\Processor\Action;
use Router\Processor\ProcessorInterface;

class CallableProcessorTest extends TestCase
{
    private ServerRequestInterface $request;

    public function setUp(): void
    {
        $this->request = ServerRequest::fromGlobals();
    }
    public function testExecuteCallbackProcessorWithoutCallbackThrowException()
    {
        $processor = new Callback('');
        $this->expectException(Exception::class);
        $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler($this->request));
    }

    public function testCallReturnResponseInterface()
    {
        $processor = new Callback(fn() => '');
        $result = $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler($this->request));
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testCallReturnResponseCanContainsExternalContent()
    {
        $processor = new Callback(fn() => '');
        $response = $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler($this->request,'hello'));
        $this->assertStringContainsString('hello', $response->getBody());
    }

    public function testExecuteExecutesCallable()
    {
        $processor = new Callback(fn() => 'hello');
        $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler($this->request));
        $this->assertStringContainsString('hello', $this->request->getAttribute('result'));
        $callback = function ($params) {
            return 'hello ' . implode(', ', $params);
        };

        $processor = new Callback($callback, [1, 2, 3]);
        $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler($this->request));
        $this->assertStringContainsString('hello 1, 2, 3', $this->request->getAttribute('result'));

        $className = Illusion::withClass('FakeController')
            ->use('GuzzleHttp\Psr7\Response')
            ->withMethod('test', "return 'hello 1, 2, 3';")
            ->project();

        $processor = new Controller([$className, 'test'], [1, 2, 3]);
        $this->request = ServerRequest::fromGlobals();
        $processor->process($this->request, $this->makeRequestHandler($this->request));
        $this->assertStringContainsString('hello 1, 2, 3', $this->request->getAttribute('result') ?? '');

        $className = Illusion::withClass('FakeController')
            ->use('GuzzleHttp\Psr7\Response')
            ->extends(BaseController::class)
            ->withMethod('test', "return 'hello 1, 2, 3';")
            ->project();

        $processor = new Controller([$className, 'test'], [1, 2, 3]);
        $processor->process(ServerRequest::fromGlobals(), $this->makeRequestHandler($this->request));
        $this->assertStringContainsString('hello 1, 2, 3', $this->request->getAttribute('result') ?? '');

        $className = Illusion::withClass('FakeAction')
            ->use('GuzzleHttp\Psr7\Response')
            ->withMethod('handle', "return 'hello 1, 2, 3';")
            ->project();
        $processor = new Action($className, [1, 2, 3]);
        $processor->process($this->request, $this->makeRequestHandler($this->request));
        $this->assertStringContainsString('hello 1, 2, 3', $this->request->getAttribute('result'));
    }

    private function makeRequestHandler(ServerRequestInterface $request, $content = '')
    {
        $builder = $this->getMockBuilder(RequestHandlerInterface::class);
        $builder->allowMockingUnknownTypes();
        $mock = $builder->getMock();
        $mock->method('handle')->willReturnCallback(function($request) use ($mock, $content){
            $this->request = $request;
           return new Response(200, [], $content);
        });

        return $mock;
    }
}
