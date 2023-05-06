<?php

namespace Router;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Router register Route(s) and parse them
 * with method run
 */
class Router implements RouterInterface
{
    private $authorizedMethods = [];
    private $routes = array();

    /**
     * @throws RouterException
     */
    public function addRoute(string $method, string $path, string $name, $callable): Route
    {
        if (!$method) {
            throw new RouterException('Try to add a route without Http method');
        }
        if (!$this->authorize($method)) {
            throw new RouterException("Try to add a route with an unauthorized method");
        }

        if ($this->isNamedRouteExists($method, $name)) {
            throw new RouterException('Route name conflict when loading almost two routes have same method and same name');
        }

        if (!array_key_exists($method, $this->routes)) {
            $this->routes[$method] = [];
        }

        $route = new Route($path, $name, $callable);
        $this->routes[$method][$name] = $route;

        return $route;
    }

    /**
     * @throws RouterException
     */
    public function any(string $path, string $name, $callable): array
    {
        $routes = $this->some($this->getAuthorizedMethods(), $path, $name, $callable);

        return $routes;
    }

    /**
     * @throws RouterException
     */
    public function some($methods, $path, $name, $callable): array
    {
        $routes = [];
        foreach ($methods as $method) {
            $route = $this->addRoute($method, $path, $name, $callable);
            $routes[$method] = $route;
        }

        return $routes;
    }

    /**
     * @throws RouterException
     */
    public function get(string $path, string $name, $callable): Route
    {
        return $this->addRoute('GET', $path, $name, $callable);
    }

    /**
     * @throws RouterException
     */
    public function post(string $path, string $name, $callable): Route
    {
        return $this->addRoute('POST', $path, $name, $callable);
    }

    /**
     * @throws RouterException
     */
    public function put(string $path, string $name, $callable): Route
    {
        return $this->addRoute('PUT', $path, $name, $callable);
    }

    /**
     * @throws RouterException
     */
    public function delete(string $path, string $name, $callable): Route
    {
        return $this->addRoute('DELETE', $path, $name, $callable);
    }

    /**
     * @throws RouterException
     */
    public function options(string $path, string $name, $callable): Route
    {
        return $this->addRoute('OPTIONS', $path, $name, $callable);
    }

    public function match(ServerRequestInterface $request): Route
    {
        $method = $request->getMethod();
        if (!isset($this->routes[$method])) {
            throw new RouterException('Router method not supported');
        }

        //Walk through the routes
        $path = $request->getUri()->getPath();
        foreach ($this->routes[$method] as $route) {
            if ($route->match($path)) {
                return $route;
            }
        }

        return new Route;
    }

    public function getAuthorizedMethods(): array
    {
        return $this->authorizedMethods;
    }

    public function setAuthorizedMethods(array $authorizedMethods): self
    {
        $this->authorizedMethods = $authorizedMethods;
        return $this;
    }

    public function authorize(string $method)
    {
        return in_array($method, $this->authorizedMethods);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function setRoutes(array $routes): Router
    {
        $this->routes = array_merge_recursive($this->routes, $routes);
        return $this;
    }

    public function isNamedRouteExists($method, $name)
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
