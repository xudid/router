<?php

namespace test;

use PHPUnit\Framework\TestCase;
use xudid\Router\Router;

class RouterTest extends TestCase
{
    private $router;

    public function testConstruct()
    {
        self::assertInstanceOf(Router::class, $this->router);
    }

    public function testGet()
    {
        $this->router->get();
    }

    public function testPost()
    {

    }

    public function testProcess()
    {

    }

    public function testGenerateUrl()
    {

    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();
    }

}
