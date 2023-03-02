<?php

use Core\Controller\BaseController;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Illusion\Illusion;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Router\Processor\Callback;
use Router\Processor\Controller;
use Router\Processor\Action;

class CallableProcessorTest extends TestCase
{
    public function testSetParamsIsFluent()
    {
        $callable = new Callback(new Response());
        $result = $callable->setParams([]);
        $this->assertInstanceOf(Callback::class, $result);
    }

    public function testSetParamsArgumentIsArray()
    {
        $callable = new Callback(new Response());
        $this->expectException(TypeError::class);
        $callable->setParams('test');
    }

    public function testExecuteCallbackProcessorWithoutCallbackThrowException()
    {
        $processor = new Callback(new Response());
        $this->expectException(Exception::class);
        $processor->execute();
    }

    public function testCallReturnResponseInterface()
    {
        $processor = new Callback(new Response());
        $processor->setCallable(fn() => '');
        $result = $processor->execute();
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testCallReturnResponseCanContainsExternalContent()
    {
        $processor = new Callback(new Response(200, [], 'hello'));
        $processor->setCallable(fn() => '');

        $result = $processor->execute();
        $this->assertStringContainsString('hello', $result->getBody()->getContents());
    }

    public function testsetCallableIsFluent()
    {
        $processor = new Callback(new Response());
        $result = $processor->setCallable(fn()=> '');
        $this->assertInstanceOf(Callback::class, $result);
    }

    public function testExecuteExecutesCallable()
    {
        $processor = new Callback(new Response());
        $processor->setCallable(fn() => 'hello');
        $result = $processor->execute();
        $this->assertStringContainsString('hello', $result->getBody());

        $processor = new Callback(new Response());
        $processor->setParams([1, 2, 3]);
        $processor->setCallable(function ($params) {
            return 'hello ' . implode(', ', $params);
        });
        $result = $processor->execute();
        $this->assertStringContainsString('hello 1, 2, 3', $result->getBody());

        $className = Illusion::withClass('FakeController')
            ->use('GuzzleHttp\Psr7\Response')
            ->withMethod('test', "return new Response(200, [], 'hello 1, 2, 3');")
            ->project();

        $processor = new Controller(ServerRequest::fromGlobals(), new Response());
        $processor->setParams([1, 2, 3]);
        $processor->setCallable([$className, 'test']);
        $result = $processor->execute();
        $this->assertStringContainsString('hello 1, 2, 3', $result->getBody());

        $className = Illusion::withClass('FakeController')
            ->use('GuzzleHttp\Psr7\Response')
            ->extends(BaseController::class)
            ->withMethod('test', "return new Response(200, [], 'hello 1, 2, 3');")
            ->project();

        $processor = new Controller(ServerRequest::fromGlobals(), new Response());
        $processor->setParams([1, 2, 3]);
        $processor->setCallable([$className, 'test']);
        $result = $processor->execute();
        $this->assertStringContainsString('hello 1, 2, 3', $result->getBody());

        $className = Illusion::withClass('FakeAction')
            ->use('GuzzleHttp\Psr7\Response')
            ->withMethod('handle', "return 'hello 1, 2, 3';")
            ->project();

        $processor = new Action(new Response());
        $processor->setParams([1, 2, 3]);
        $processor->setCallable($className);
        $result = $processor->execute();
        $this->assertStringContainsString('hello 1, 2, 3', $result->getBody());
    }
}
