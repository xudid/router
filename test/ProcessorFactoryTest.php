<?php

namespace Test;

use Illusion\Illusion;
use Router\Processor\Action;
use Router\Processor\Callback;
use Router\Processor\Factory;
use Router\Processor\ProcessorInterface;
use Router\Route;

class ProcessorFactoryTest extends \Test\Factory
{
    public function testCreateMethodExists()
    {
        $result = method_exists(Factory::class, 'create');
        $this->assertTrue($result);
    }

    public function testCreateReturnInstanceOfProcessorInterface()
    {
        $route = new Route('simple/new', 'simple_new', fn() => '');
        $factory = $this->makeFactory();
        $result = $factory->create($route);
        $this->assertInstanceOf(ProcessorInterface::class, $result);
    }

    public function testCreateReturnCallbackProcessor()
    {
        $route = new Route('simple/new', 'simple_new', fn() => '');
        $factory = $this->makeFactory();
        $result = $factory->create($route);
        $this->assertInstanceOf(Callback::class, $result);
    }

    public function testReturnActionProcessor()
    {
        $className = Illusion::withClass('Action')
            ->withMethod('handle', "return 'hello 1, 2, 3';")
            ->project();
        $route = new Route('simple/new', 'simple_new', $className);
        $factory = $this->makeFactory();
        $result = $factory->create($route);
        $this->assertInstanceOf(Action::class, $result);
    }
}
