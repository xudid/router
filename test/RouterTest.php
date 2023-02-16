<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Router\Route;
use Router\Router;
use Router\RouterException;

class RouterTest extends TestCase
{
    private $router;

	protected function setUp(): void
	{
		parent::setUp();
		$this->router = new Router();
		$this->router->setAuthorizedMethods(['GET','POST']);
	}

    public function testConstruct()
    {
        self::assertInstanceOf(Router::class, $this->router);
    }

    public function testThrowUnAuthorizedMethodExceptionOn()
    {
        $this->expectException(RouterException::class);
        $router = new Router();
        $router->get('/root', 'root', fn() => '');
    }

    public function testGet()
    {
        $route = $this->router->get('/root', 'root', fn() => '');
        self::assertInstanceOf(Route::class,$route);
    }

    public function testPost()
    {
        $route = $this->router->post('/root', 'root', fn() => '');
        self::assertInstanceOf(Route::class,$route);
    }

    public function testGenerateUrl()
    {
        $this->router->get('/simple/:id','simple_show', fn() => '')
			->with('id','[\d+]');
        $url = $this->router->generateUrl('simple_show', ['id' =>3]);

        self::assertEquals('/simple/3',$url);
        $this->router->get('/simple1/:id/:a_id','simple_show_2', fn() => '')
			->with('id','[\d+]')->with('a_id','[\d+]');
        $url = $this->router->generateUrl( 'simple_show_2',['id' =>3,'a_id' =>2]);
        self::assertEquals('/simple1/3/2',$url);
    }
}
