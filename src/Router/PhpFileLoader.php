<?php

namespace Router;

use Core\FileSystem\Path;
use Exception;

class PhpFileLoader
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
	public function load(string $filePath): array
	{
        $absolutePath = Path::absolute($filePath);
        $config = require($absolutePath);
        $authorizedMethods = $config['authorized_methods'] ?? [];
        $this->router->setAuthorizedMethods($authorizedMethods);

        $hydratedRoutes = [];
        $routes = $config['routes'] ?? [];
        foreach ($routes as $route) {
            if (!$this->routeHasKey($route,'method')) {
                throw new Exception('Try to load a route without Http method');
            }

            $method = $route['method'];
            if (!$this->router->authorize($method)) {
                throw new RouterException("Try to add a route with an unauthorized method");
            }

            if (
                array_key_exists($method, $hydratedRoutes)
                && $this->routeHasKey($route,'name')
                && $route['name']
                && array_key_exists($route['name'], $hydratedRoutes[$method])
            ) {
                throw new Exception('Route name conflict when loading almost two routes have same method and same name');
            }

				$route = Route::hydrate($route);
				if ($route->getName()) {
					$hydratedRoutes[$method][$route->getName()] = $route;
				} else {
					$hydratedRoutes[$method][] = $route;
				}
		}

        $this->router->setRoutes($hydratedRoutes);
		return $hydratedRoutes;
	}

	private function routeHasKey($route, $key): bool
	{
		return array_key_exists($key, $route);
	}
}
