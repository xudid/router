<?php

namespace Router;

use Exception;

class Loader
{
	private Router $router;

	/**
	 * @param Router $router
	 */
	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	/**
	 * @param array $routes
	 * @return array
	 * @throws Exception
	 */
	public function load(array $routes): array
	{
		$hydratedRoutes = [];
		foreach ($routes as $route) {
			$method = $route['method'] ?: '';
			if ($this->router->authorize($method)) {
				if ($this->routeHasKey($route,'method')) {
					$method = $route['method'];
				} else {
					throw new Exception('Try to load a route without Http method');
				}
				$name = false;
				if ($this->routeHasKey($route,'name')) {
					$name = $route['name'];
				}

				if (
					array_key_exists($method, $hydratedRoutes)
					&& $name
					&& array_key_exists($name, $hydratedRoutes[$method])
				) {
					throw new Exception('Route name conflict when loading almost two routes have same method and same name');
				}

				$route = Route::hydrate($route);
				if ($name) {
					$hydratedRoutes[$method][$name] = $route;
				} else {
					$hydratedRoutes[$method][] = $route;
				}
			}
		}
		return $hydratedRoutes;
	}

	private function routeHasKey($route, $key): bool
	{
		return array_key_exists($key, $route);
	}
}
