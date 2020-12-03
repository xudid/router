<?php

namespace test;

use PHPUnit\Framework\TestCase;
use Router\Route;
use Router\Router;
use Router\RouterException;

class RouterTest extends TestCase
{
    private $router;

    public function testConstruct()
    {
        self::assertInstanceOf(Router::class, $this->router);
    }

    public function testThrowUnAuthorizedMethodExceptionOn()
    {
        $this->expectException(RouterException::class);
        $router = new Router();
        $route = $router->get("test", "/root", "root", function(){echo "hello world";});
    }

    public function testGet()
    {
        $route = $this->router->get("test", "/root", "root", function(){echo "hello world";});
        self::assertInstanceOf(Route::class,$route);
    }

    public function testPost()
    {
        $route = $this->router->post("test", "/root", "root", function(){echo "hello world";});
        self::assertInstanceOf(Route::class,$route);
    }



    public function testGenerateUrl()
    {
        $this->router->get("test","/simple/:id","simple_show",function(){return true;})->with("id","[\d+]");
        $url = $this->router->generateUrl("test", "simple_show",["id" =>3]);
        self::assertEquals("/simple/3",$url);
        $this->router->get("test","/simple1/:id/:a_id","simple_show_2",function(){return true;})->with("id","[\d+]")->with("a_id","[\d+]");
        $url = $this->router->generateUrl("test", "simple_show_2",["id" =>3,"a_id" =>2]);
        self::assertEquals("/simple1/3/2",$url);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();
        $this->router->setAuthorizedMethods(["GET","POST"]);
    }

}
