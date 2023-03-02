<?php

use Router\PhpFileLoader;
use PHPUnit\Framework\TestCase;
use Router\Route;

class LoaderTest extends TestCase
{
	private function getLoader()
	{
		$router = new Router\Router();
		$router->setAuthorizedMethods(['GET']);
		return new PhpFileLoader($router);
	}

	public function testLoad()
	{
		$loader = $this->getLoader();
		$loadedRoutes = $loader->load('test/ressources/dat_set1.php');
		$this->assertArrayHasKey('GET',$loadedRoutes);
		$this->assertArrayHasKey('test_show',$loadedRoutes['GET']);
		$this->assertInstanceOf(Route::class, $loadedRoutes['GET']['test_show']);
	}

	public function testLoadAnonymousRoutes()
	{
		$loader = $this->getLoader();
		$loadedRoutes = $loader->load('test/ressources/data_set2.php');
		$this->assertCount(2, $loadedRoutes['GET']);
	}
}
