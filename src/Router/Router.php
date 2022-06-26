<?php

namespace Router;


use Exception;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Router register Route(s) and parse them
 * with method run
 */
class Router
{
    private $authorizedMethods = [];
    /**
     * @var array $routes
     */
    private $routes = array();

    /**
     * @param string $method
     * @param string $path
     * @param string $name
     * @param $callable
     * @return Route
     * @throws RouterException
     */
    public function addRoute(
        string $method,
        string $path,
        string $name,
        $callable
    ): Route
    {
        if ($this->authorize($method)) {
            if (!array_key_exists($method, $this->routes)) {
                $this->routes[$method] = [];
            }
            $route = new Route($path, $name, $callable);
            $this->routes[$method][$name] = $route;
            return $route;
        } else {
            throw new RouterException("Try to add a route with an unauthorized method");
        }

    }

    /**
     * Register a Route to a ressource asked with a HTTP POST method
     * @param string $path the actual url path to the ressource with beginning '/'
     * @param string|null $name string to display in menu
     * @param array|callable $callable callback function to call if route match
     * the array contains in order an object , the name of a function to call
     * on this object , the callback function to call after that
     * @return Route
     *
     * @throws RouterException
     */
    public function get(
        string $path,
        string $name,
        $callable
    ): Route
    {

        return $this->addRoute("GET", $path, $name, $callable);

    }

    /**
     * Register a Route to a ressource asked with a HTTP POST method
     * @param string $path the actual url path to the ressource with beginning '/'
     * @param string|null $name string to display in menu
     * @param array|callable $callable callback function to call if route match
     * the array contains in order an object , the name of a function to call
     * on this object , the callback function to call after that
     * @return Route
     *
     * @throws RouterException
     */
    public function post(
        string $path,
        string $name,
        $callable
    ): Route
    {
        return $this->addRoute("POST", $path, $name, $callable);
    }

    /**
     * @param string $path
     * @param string $name
     * @param $callable
     * @return Route
     * @throws RouterException
     */
    public function put(
        string $path,
        string $name,
        $callable
    ): Route
    {
        return $this->addRoute("PUT", $path, $name, $callable);
    }

    /**
     * @param string $path
     * @param string $name
     * @param $callable
     * @return Route
     * @throws RouterException
     */
    public function delete(
        string $path,
        string $name,
        $callable
    ): Route
    {
        return $this->addRoute("DELETE", $path, $name, $callable);
    }

    /**
     * @param string $path
     * @param string $name
     * @param $callable
     * @return Route
     * @throws RouterException
     */
    public function options(
        string $path,
        string $name,
        $callable
    ): Route
    {
        return $this->addRoute("OPTIONS", $path, $name, $callable);
    }

    public function match(ServerRequestInterface $request): ?Route
    {
        $method = $request->getMethod();
        $matchedRoute = null;
        //Enigmatic request
        if (!isset($this->routes[$method])) {
            return $matchedRoute;
        }

        //Walk through the routes
        foreach ($this->routes[$method] as $name => $route) {
            if ($route->match($request->getUri()->getPath())) {
                $matchedRoute = $route;
                break;
            }
        }

        return $matchedRoute;
    }

    /**
     * hasParams test if the path of the route has params
     * like ":id" return true  if has params else return false
     * @param string $url [description]
     * @return bool        [description]
     */
    private function hasParams(string $url): bool
    {
        return preg_match('#:([\w]+)#', $url, $matches) ? true : false;
    }

    /**
     * @param string $name the name of the we want to generate an url from
     * @param array $params
     * @param string $method
     * @return string
     * @throws Exception
     * @throw RouterException
     */
    public function generateUrl(string $name, array $params = [], string $method = 'GET')
    {
        //Does it need an isRouteExist($method,$name):bool  ?
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
        } else {
            throw new Exception(
                sprintf(
                    'Can not generate url, Route with name %s and method %s does not exist',
                    $name,
                    $method
                )
            );
        }
        return $url;
    }

    /**
     * @return array
     */
    public function getAuthorizedMethods(): array
    {
        return $this->authorizedMethods;
    }


    /**
     * @param array $authorizedMethods
     */
    public function setAuthorizedMethods(array $authorizedMethods): self
    {
        $this->authorizedMethods = $authorizedMethods;
        return $this;
    }

    public function authorize(string $method)
    {
        return in_array($method, $this->authorizedMethods);
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param array $routes
     * @return Router
     */
    public function setRoutes(array $routes): Router
    {
        $this->routes = array_merge_recursive($this->routes, $routes);
        return $this;
    }


    public function getRoute(string $method, string $name)
    {
        if (!array_key_exists($method, $this->routes)) {
            return false;
        }
        if (array_key_exists($name, $this->routes[$method])) {
            return $this->routes[$method][$name];
        }
        return false;
    }
}
