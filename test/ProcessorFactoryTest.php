<?php

namespace Router;

use Illusion\Illusion;
use PHPUnit\Framework\TestCase;
use Router\Processor\Action;
use Router\Processor\Callback;
use Router\Processor\Factory;
use Router\Processor\ProcessorInterface;

class ProcessorFactoryTest extends TestCase
{
    public function testCreateMethodExists()
    {
        $result = method_exists(Factory::class, 'create');
        $this->assertTrue($result);
    }

    public function testCreateReturnInstanceOfProcessorInterface()
    {
        $route = new Route('simple/new', 'simple_new', fn() => '');
        $result = Factory::create($route);
        $this->assertInstanceOf(ProcessorInterface::class, $result);
    }

    public function testCreateReturnCallbackProcessor()
    {
        $route = new Route('simple/new', 'simple_new', fn() => '');
        $result = Factory::create($route);
        $this->assertInstanceOf(Callback::class, $result);
    }

    public function testReturnActionProcessor()
    {
        $className = Illusion::withClass('Action')
            ->withMethod('handle', "return 'hello 1, 2, 3';")
            ->project();
        $route = new Route('simple/new', 'simple_new', $className);
        $result = Factory::create($route);
        $this->assertInstanceOf(Action::class, $result);
    }
}
