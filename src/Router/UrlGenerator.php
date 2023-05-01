<?php

namespace Router;

use Exception;

class UrlGenerator
{

    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function generate(string $name, array $params = [], string $method = 'GET', $baseUrl = ''): string
    {
        $route = $this->getRoute($method, $name);
        if ($route) {
            $path = $route->getPath();
            $routeParams = $route->getParams();

            foreach ($routeParams as $key => $value) {
                if (array_key_exists($key, $params)) {
                    $pattern = "#(:" . $key . ")#";
                    $replacement = $params[$key];
                    $path = preg_replace($pattern, $replacement, $path);
                }
            }
            $url = '/' . $path;
        }

        if ($baseUrl) {
            $url = $baseUrl . $url;
        }

        return $url;
    }

    public function getRoute(string $method, string $name)
    {
        $routeExists = $this->isNamedRouteExists($method, $name);
        if (!$routeExists) {
            throw new Exception(
                sprintf(
                    'Can not generate url, Route with name %s and method %s does not exist',
                    $name,
                    $method
                )
            );
        }

        return $this->routes[$method][$name];
    }

    public function isNamedRouteExists($method, $name): bool
    {
        if (!array_key_exists($method, $this->routes)) {
            return false;
        }

        if (!array_key_exists($name, $this->routes[$method])) {
            return false;
        }

        return true;
    }
}
