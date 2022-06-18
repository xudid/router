<?php

use Router\Loader;
use PHPUnit\Framework\TestCase;
use Router\Route;

class LoaderTest extends TestCase
{
	private function getLoader()
	{
		$router = new Router\Router();
		$router->setAuthorizedMethods(['GET']);
		return new Loader($router);
	}

	public function testLoad()
	{
		$route = [
			[
				'action' => [
					'description' => 'test',
					'type' => 'SHOW'
				],
				'method' => 'GET',
				'name' => 'test_show',
				'path' => '/test/:id',
				'callback' => function () {
					echo "loaded";
				},
				'params' => [['id' => '[0-9]+']]
			]
		];
		$loader = $this->getLoader();
		$loadedRoutes = $loader->load($route);
		$this->assertArrayHasKey('GET',$loadedRoutes);
		$this->assertArrayHasKey('test_show',$loadedRoutes['GET']);
		$this->assertInstanceOf(Route::class, $loadedRoutes['GET']['test_show']);
	}

	public function testLoadAnonymousRoutes()
	{
		$routes = [
			[
				'method' => 'GET',
				'path' => '/test/:id',
				'callback' => function () {
					echo "loaded";
				},
			],
			[
				'method' => 'GET',
				'path' => '/test2/:id',
				'callback' => function () {
					echo "loaded2";
				},
			]
		];
		$loader = $this->getLoader();
		$loadedRoutes = $loader->load($routes);
		$this->assertCount(count($routes), $loadedRoutes['GET']);
	}
}
