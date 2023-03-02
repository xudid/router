<?php

namespace Test;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
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
		$this->router->setAuthorizedMethods(['GET','POST', 'PUT', 'DELETE', 'OPTIONS']);
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
        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('GET', $routes);
        $this->assertCount(1, $routes['GET']);
    }

    public function testPost()
    {
        $route = $this->router->post('/root', 'root', fn() => '');
        self::assertInstanceOf(Route::class,$route);
        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('POST', $routes);
        $this->assertCount(1, $routes['POST']);
    }

    public function testPut()
    {
        $route = $this->router->put('/root', 'root', fn() => '');
        self::assertInstanceOf(Route::class,$route);
        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('PUT', $routes);
        $this->assertCount(1, $routes['PUT']);
    }

    public function testDelete()
    {
        $route = $this->router->delete('/root', 'root', fn() => '');
        self::assertInstanceOf(Route::class,$route);
        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('DELETE', $routes);
        $this->assertCount(1, $routes['DELETE']);
    }

    public function testOptions()
    {
        $route = $this->router->options('/root', 'root', fn() => '');
        self::assertInstanceOf(Route::class,$route);
        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('OPTIONS', $routes);
        $this->assertCount(1, $routes['OPTIONS']);
    }

    public function testAny()
    {
        $result = $this->router->any('/root', 'root', fn() => '');
        $routes = $this->router->getRoutes();

        $this->assertIsArray($result);
        $this->assertIsArray($routes);
        foreach ($this->router->getAuthorizedMethods() as $authorizedMethod) {
            $this->assertArrayHasKey($authorizedMethod, $routes);
            $this->assertArrayHasKey($authorizedMethod, $result);
            $this->assertCount(1, $routes[$authorizedMethod]);
        }
    }

    public function testSome()
    {
        $methods = ['GET', 'POST'];
        $result = $this->router->some($methods, '/root', 'root', fn() => '');

        $routes = $this->router->getRoutes();

        $this->assertIsArray($result);
        $this->assertCount(2, $routes);
        $this->assertIsArray($routes);

        foreach ($methods as $method) {
            $this->assertArrayHasKey($method, $routes);
            $this->assertArrayHasKey($method, $result);
            $this->assertCount(1, $routes[$method]);
        }

    }

    public function testNotMatchThrowException()
    {
        $this->router->post('/root', 'root', fn() => '');
        $mockRequest = $this->getMockBuilder(ServerRequest::class);
        $mockRequest->disableOriginalConstructor();
        $this->expectException(RouterException::class);
        $this->router->match($mockRequest->getMock());
    }

    public function testNotMatchWithHttpMetthod()
    {
        $this->router->post('/root', 'root', fn() => '');
        $mockRequest = $this->getMockBuilder(ServerRequest::class);
        $mockRequest->setConstructorArgs(['GET', '/root']);
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Router method not supported');
        $this->router->match($mockRequest->getMock());
    }

    public function testNotMatchRouteNotFound()
    {
        $mockRequest = $this->getMockBuilder(ServerRequest::class);
        $mockRequest->disableOriginalConstructor();
        $mock = $mockRequest->getMock();
        $mock->method('getMethod')->willReturn('GET');
        $uriMock = $this->getMockBuilder(Uri::class)->getMock();
        $uriMock->method('getPath')->willReturn('/root');
        $mock->method('getUri')->willReturn($uriMock);

        $this->router->get('/root2', 'root', fn() => '');
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Route not found');
        $this->router->match($mock);
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
