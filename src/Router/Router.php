<?php
namespace Router;


use Psr\Http\Message\ServerRequestInterface;

/**
 * Router register Route(s) and parse them
 * with method run
 */
class Router
{
    /**
     * @var string $url
     */
    private $url = null;

    private $authorizedMethods = [];
    /**
     * @var array $routes
     */
    private $routes = array();

    /**
     * @param string $method
     * @param string $scope
     * @param string $path
     * @param string $name
     * @param $callable
     * @return Route
     * @throws RouterException
     */
    private function addRoute(
        string $method,
        string $scope,
        string $path,
        string $name,
        $callable
    ): Route
    {
        if (in_array($method, $this->authorizedMethods)) {
            $route = new Route($path, $name, $callable);
            $this->routes[$method][$scope][$name] = $route;
            return $route;
        } else {
            throw new RouterException("Try to add a route with an unauthorized method");
        }

    }

    /**
     * Register a Route to a ressource asked with a HTTP POST method
     * @param string $scope the logic url domain without a '/'
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
        string $scope,
        string $path,
        string $name,
        $callable
    ): Route
    {

        return $this->addRoute("GET", $scope, $path, $name, $callable);

    }

    /**
     * Register a Route to a ressource asked with a HTTP POST method
     * @param string $scope the logic url domain without a '/'
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
        string $scope,
        string $path,
        string $name,
        $callable
    ): Route
    {

        return $this->addRoute("POST", $scope, $path, $name, $callable);

    }

    /**
     * @param string $scope
     * @param string $path
     * @param string $name
     * @param $callable
     * @return Route
     * @throws RouterException
     */
    public function put(
        string $scope,
        string $path,
        string $name,
        $callable
    ): Route
    {

        return $this->addRoute("PUT", $scope, $path, $name, $callable);

    }

    /**
     * @param string $scope
     * @param string $path
     * @param string $name
     * @param $callable
     * @return Route
     * @throws RouterException
     */
    public function delete(
        string $scope,
        string $path,
        string $name,
        $callable
    ): Route
    {

        return $this->addRoute("DELETE", $scope, $path, $name, $callable);

    }

    /**
     * @param string $scope
     * @param string $path
     * @param string $name
     * @param $callable
     * @return Route
     * @throws RouterException
     */
    public function options(
        string $scope,
        string $path,
        string $name,
        $callable
    ): Route
    {

        return $this->addRoute("OPTIONS", $scope, $path, $name, $callable);

    }

    public function match(ServerRequestInterface $request): ?Route
    {
        $this->url = ($request->getUri()->getPath());
        $parts = explode('/', $this->url);
        $scope = $parts[0];
        if (empty($scope)) {
            $scope = "default";
        }
        $method = $request->getMethod();
        $matchedRoute = null;
        //Enigmatic request
        if (!isset($this->routes[$method][$scope])) {
            return $matchedRoute;
        }

        //Walk through the routes
        foreach ($this->routes[$method][$scope] as $name => $route) {
            if ($route->match($request)) {
                $matchedRoute =  $route;
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
     * @param array $param parameters needed to generate this url
     * @return string
     * @throw RouterException
     */
    public function generateUrl(string $scope, string $name, array $params = [])
    {
        $url = "test";
        //Does it need an isRouteExist($method,$scope,$name):bool  ?
        if (array_key_exists('GET', $this->routes) &&
            array_key_exists($scope, $this->routes['GET']) &&
            array_key_exists($name, $this->routes['GET'][$scope])) {

            $route = $this->routes['GET'][$scope][$name];
            $path = $route->getPath();
            $routeParams = $route->getParams();
            foreach ($params as $key => $value) {
                if (array_key_exists($key, $routeParams)) {
                    $pattern = "#(:" . $key . ")#";
                    $replacement = $params[$key];
                    $path = preg_replace($pattern, $replacement, $path);
                }
            }
            $url = '/' . $path;
        }

        return $url;

    }

    /**
     * @param array $authorizedMethods
     */
    public function setAuthorizedMethods(array $authorizedMethods): self
    {
        $this->authorizedMethods = $authorizedMethods;
        return $this;
    }


}


?>
