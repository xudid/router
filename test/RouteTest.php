<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Router\Parameter;
use Router\Route;

class RouteTest extends TestCase
{
    public function testCanConstruct()
    {
        $route = new Route('/admin', 'route1', fn() => '');
        self::assertNotNull($route, 'Route constructor must return an object instance');
        self::assertInstanceOf(Route::class, $route, 'Route constructor must return an object of Router/Route::class');
    }

    public function testGetNameReturnString()
    {
        $route = new Route('/admin', 'route1', fn() => '');
        $name = $route->getName();
        self::assertEquals('route1', $name);
    }

    public function testMatchReturnBool()
    {
        $route = new Route('/admin', 'admin', fn() => '');
        $matched = $route->match('/');
        self::assertIsBool($matched);
    }

    public function testWithReturnSelf()
    {
        $route = new Route('/admin', 'admin', fn() => '');
        $r = $route->with('id', '\d+');
        self::assertEquals($route, $r);
    }

    public function testMatchSimpleRoute()
    {
        $routes = [
            new Route('/simple1', 'route1', fn() => ''),
            new Route('/simple', 'route2', fn() => '')
        ];
        $matched = null;
        foreach ($routes as $route) {
            if ($route->match('/simple')) {
                $matched = $route;
                break;
            }
        }

        self::assertEquals('route2', $matched->getName());
    }

    public function testRootRouteMatch()
    {
       $route = new Route('/', 'root', fn() => '');
        $matched = $route->match('/');
        self::assertTrue($matched);
    }

    public function testRouteGetCallbackReturn()
    {
        $route = new Route('/', 'root', fn() => '');
        $callback = $route->getCallback();
        $this->assertInstanceOf(\Closure::class , $callback);

        $route = new Route('/', 'root');
        $callback = $route->getCallback();
        $this->assertNull($callback);
    }

    public function testNotMatchInteger()
    {
        $route = new Route('/', 'root', fn() => '');
        $matched = $route->match(1);
        self::assertFalse($matched);
    }

    public function testMatchSimpleRouteWithTrailingSlash()
    {
        $route = new Route('simple', 'simple', fn() => '');
        $matched = $route->match('simple/');
        self::assertTrue($matched);
    }

    public function testConstructRouteCompilePattern()
    {
        $route = new Route('simple/:id/edit/:id2', 'test', fn() => '');
        $pattern = $route->getPattern();
        $this->assertEquals('simple/(?<id>[\w]+)/edit/(?<id2>[\w]+)', $pattern);

        $route = new Route('simple/:id/edit/:id2', 'test', fn() => '');
        $route->with('id', Parameter::INT)
            ->with('id2', Parameter::ALPHA);
        $pattern = $route->getPattern();
        $this->assertEquals('simple/(?<id>[\d]+)/edit/(?<id2>[a-z,A-Z]+)', $pattern);

        $routeData = [
            'method' => 'GET',
            'name' => 'users_show',
            'path' => '/users/:id',
            'callback' => fn() => '',
            'params' => [['id' => '[0-9]+']]
        ];

        $route = Route::hydrate($routeData);
        $pattern = $route->getPattern();
        $this->assertEquals('users/(?<id>[0-9]+)', $pattern);
    }
    public function testConstructRouteWithParamsInPathAddParams()
    {
        $route = new Route('simple/:id/edit', 'simple_edit', fn() => '');

        $routeParams = $route->getParams();
        $this->assertNotEmpty($routeParams);
        $this->assertArrayHasKey('id', $routeParams);
        $this->assertCount(1, $routeParams);

        $route = new Route('simple/:id2/edit', 'simple_edit', fn() => '');

        $routeParams = $route->getParams();
        $this->assertNotEmpty($routeParams);
        $this->assertCount(1, $routeParams);
        $this->assertArrayHasKey('id2', $routeParams);

        $route = new Route('simple/:id2/edit/:id3', 'simple_edit', fn() => '');

        $routeParams = $route->getParams();
        $this->assertNotEmpty($routeParams);
        $this->assertCount(2, $routeParams);
        $this->assertArrayHasKey('id2', $routeParams);
        $this->assertArrayHasKey('id3', $routeParams);

        $route = new Route('simple/:id2/:id3', 'simple_edit', fn() =>'');

        $routeParams = $route->getParams();
        $this->assertNotEmpty($routeParams);
        $this->assertCount(2, $routeParams);
        $this->assertArrayHasKey('id2', $routeParams);
        $this->assertArrayHasKey('id3', $routeParams);
    }

    public function testMachRouteWithParam()
    {
        $route = new Route('simple/:id/edit', 'simple_edit', fn() => '');
        $route->with('id', '[0-9]+');
        $matched = $route->match('simple/1/edit');
        self::assertTrue($matched);

        $route = new Route('simple/edit/:id', 'simple_show', fn() => '');
        $route->with('id', '[\d]+');
        $matched = $route->match('simple/edit/1');
        self::assertTrue($matched);

        $route = new Route('simple/edit/:id', 'simple_show', fn() => '');
        $route->with('id', '[\d]+');
        $matched = $route->match('simple/edit/1?test=1');
        self::assertTrue($matched);

        $route = new Route('simple/edit/:id', 'simple_show', fn() => '');
        $matched = $route->match('simple/edit/1?test=1');
        self::assertTrue($matched);

        $route = new Route('simple/edit/:id', 'simple_show', fn() => '');
        $matched = $route->match('simple/edit/A1a2?test=1');
        self::assertTrue($matched);

        $route = new Route('simple/edit/:id/', 'simple_show', fn() => '');
        $matched = $route->match('simple/edit/A1a2/1?test=1');
        self::assertFalse($matched);
    }

    public function testMatchRouteWithParamsParamsValues()
    {
        $route = new Route('simple/edit/:id', 'simple_show', fn() => '' );
        $route->match('simple/edit/A1a2?test=1');
        $values = $route->getValues();
        $this->assertIsArray($values);
        $this->assertCount(1, $values);
        $this->assertArrayHasKey('id', $values);
        $this->assertEquals('A1a2', $values['id']);
    }

    public function testMatchRouteWithAction()
    {
        $route = new Route('simple/new', 'simple_new', fn() => '');
        $matched = $route->match('simple/new');

        self::assertTrue($matched);
    }

    public function testMatchWithMultipleParams()
    {
        $route = (new Route('simple/:id/association/:a_id', 'simple_new', fn() => '')
        )->with('id', '[\d]+')->with('a_id', '[\d]+');;
        $matched = $route->match('simple/1/association/2');
        self::assertTrue($matched);

        $route = (new Route('simple/:id/:a_id', 'simple_new', fn() => '')
        )->with('id', '[\d]+')->with('a_id', '[\d]+');;
        $matched = $route->match('simple/1/2');
        self::assertTrue($matched);

        $route = (new Route('simple/:a_id/:id', 'simple_new', fn() => '')
        )->with('id', '[\d]+')->with('a_id', '[\d]+');;
        $matched = $route->match('simple/1/2');
        self::assertTrue($matched);
    }

    public function testMakeName()
    {
        $className1 = 'UsersRoles';
        $className2 = 'User';
        $result1 = Route::makeName($className1, '');
        $result2 = Route::makeName($className2, '');
        $this->assertStringContainsStringIgnoringCase('users_roles',$result1);
        $this->assertStringContainsStringIgnoringCase('user',$result2);
    }

	public function testRouteWithAction()
	{
		$route = new Route('/action', '', 'Action::class');
		$this->assertIsString($route->getAction());
		$this->assertNull($route->getCallback());
		$this->assertEmpty($route->getController());
		$this->assertEmpty($route->getMethod());
	}
}
