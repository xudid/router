<?php

namespace test;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Router\{Route};

class RouteTest extends TestCase
{
    public function testCanConstruct()
    {
        $route = new Route("/admin", "route1", function () {
            echo "hello world";
        });
        self::assertNotNull($route, "Route constructor must return an object instance");
        self::assertInstanceOf(Route::class, $route, "Route constructor must return an object of Router/Route::class");
    }

    public function testGetNameReturnString()
    {
        $route = new Route("/admin", "route1", function () {
            echo "hello world";
        });
        $name = $route->getName();
        self::assertEquals("route1", $name);
    }

    public function testMatchReturnBool()
    {
        $route = new Route("/admin", "admin", function () {
            echo "hello world";
        });
        $matched = $route->match("/");
        self::assertIsBool($matched);

    }

    public function testWithReturnSelf()
    {
        $route = new Route("/admin", "admin", function () {
            echo "hello world";
        });
        $r = $route->with("id", "\d+");
        self::assertEquals($route, $r);
    }

    public function testMatchSimpleRoute()
    {
        $routes = [
            new Route("/simple1", "route1", function () {
                echo "hello world";
            }),
            new Route("/simple", "route2", function () {
                echo "hello world";
            })
        ];
        $matched = null;
        foreach ($routes as $k => $route) {
            if ($route->match("/simple")) {
                $matched = $route;
                break;
            }
        }

        self::assertEquals("route2", $matched->getName());
    }


    public function testRootRouteMatch()
    {
       $route = new Route("/", "root", function () {
            echo "hello world";
        });
        $matched = $route->match("/");
        self::assertTrue($matched);
    }

    public function testNotMatchInteger()
    {
        $route = new Route("/", "root", function () {
            echo "hello world";
        });
        $matched = $route->match(1);
        self::assertFalse($matched);
    }


    public function testMatchSimpleRouteWithTrailingSlash()
    {
        $route = new Route("simple", "simple", function () {
            echo "hello world";
        });
        $matched = $route->match("simple/");
        self::assertTrue($matched);
    }

    public function testMachRouteWithParam()
    {

        $route = (new Route("simple/:id/edit", "simple_edit", function () {
            echo "hello world";
        }))->with('id', '[0-9]+');
        $matched = $route->match("simple/1/edit");
        self::assertTrue($matched);


        $route = (new Route("simple/edit/:id", "simple_show", function () {
            echo "hello world";
        }))->with('id', '[\d]+');
        $matched = $route->match("simple/edit/1");
        self::assertTrue($matched);


        $route = (new Route("simple/edit/:id", "simple_show", function () {
            echo "hello world";
        }))->with('id', '[\d]+');
        $matched = $route->match("simple/edit/1?test=1");
        self::assertTrue($matched);
    }

    public function testMatchRouteWithAction()
    {
        $route = new Route("simple/new", "simple_new", function () {
            echo "hello world";
        });
        $matched = $route->match("simple/new");

        self::assertTrue($matched);
    }

    public function testMatchWithMultipleParams()
    {
        $route = (new Route("simple/:id/association/:a_id", "simple_new", function () {
            echo "hello world";
        }))->with('id', '[\d]+')->with('a_id', '[\d]+');;
        $matched = $route->match("simple/1/association/2");
        self::assertTrue($matched);

        $route = (new Route("simple/:id/:a_id", "simple_new", function () {
            echo "hello world";
        }))->with('id', '[\d]+')->with('a_id', '[\d]+');;
        $matched = $route->match("simple/1/2");
        self::assertTrue($matched);

        $route = (new Route("simple/:a_id/:id", "simple_new", function () {
            echo "hello world";
        }))->with('id', '[\d]+')->with('a_id', '[\d]+');;
        $matched = $route->match("simple/1/2");
        self::assertTrue($matched);
    }

    public function testMakeName()
    {
        $className1 = 'UsersRoles';
        $className2 = 'User';
        $result1 = Route::makeName($className1);
        $result2 = Route::makeName($className2);
        $this->assertStringContainsStringIgnoringCase('users_roles',$result1);
        $this->assertStringContainsStringIgnoringCase('user',$result2);
    }
}
