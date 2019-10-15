<?php
namespace Router;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
  * Router register Route(s) and parse them
  * with method run
  */
class Router implements MiddlewareInterface
{
  /**
   * @var string $url
   */
  private $url=null;

  private $authorizedMethods = [];
  /**
   * @var array $routes
   */
  private $routes= array();

/**
 * @var bool success
 */
 private $success = false;

 /**
 * Route
  * @var  $currentRoute;
  */
  private $currentRoute;

  private function addRoute(
                             string $method,
                             string $scope,
                             string $path,
                             string $name,
                             $callable
                           ) :Route
  {
      if(in_array($method, $this->authorizedMethods))
      {
          $route = new Route($path,$name, $callable);
          $this->routes[$method][$scope][$name]=$route;
          return $route;
      } else {
          throw new RouterException("Try to add a route with an unauthorized method");
      }

  }
  /**
   *
   */
  function __construct()
  {

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
     */
  public function get(
                        string $scope,
                        string $path,
                        string $name,
                        $callable
                    ):Route
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
 */
  public function post(
                        string $scope,
                        string $path,
                        string $name,
                        $callable
                    ):Route
 {

     return $this->addRoute("POST", $scope, $path, $name, $callable);

 }

    public function put(
        string $scope,
        string $path,
        string $name,
        $callable
    ):Route
    {

        return $this->addRoute("PUT", $scope, $path, $name, $callable);

    }

    public function delete(
        string $scope,
        string $path,
        string $name,
        $callable
    ):Route
    {

        return $this->addRoute("DELETE", $scope, $path, $name, $callable);

    }

    public function options(
        string $scope,
        string $path,
        string $name,
        $callable
    ):Route
    {

        return $this->addRoute("OPTIONS", $scope, $path, $name, $callable);

    }

    /**
     * Run registred routes exploration
     * If a route match put success attribute at true on the request
     * Set the route attribut on the request and the returnAppPage to true
     * on it .
     * Else set success attribute at false on the request
     * Return both the handled response
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler the fianl response handler
     * @return ResponseInterface
     */
  public function process(ServerRequestInterface $serverrequest, RequestHandlerInterface $handler): ResponseInterface
  {
    $response = $handler->handle($serverrequest);
    $this->url = ($serverrequest->getUri()->getPath());
    $parts = \explode('/',$this->url);
    $scope = $parts[0];
    if(empty($scope)){$scope = "default";}

    $method = $serverrequest->getMethod();

    //Enigmatic request
    if(!isset($this->routes[$method][$scope]))
    {
      $handler->handle($serverrequest
      ->withAttribute("success" , $this->success));
      return $response;
    }

    //Walk through the routes
    foreach ($this->routes[$method][$scope] as $name =>$route)
    {
        if($route->match($serverrequest))
        {
          $this->success = true;
          $this->currentRoute =$route;
          break;
        }
    }

    //We found the promise land
    if($this->success)
    {
      $handler->handle($serverrequest
      ->withAttribute("success" , $this->success)
      ->withAttribute("route", $this->currentRoute)
     );
    }
    //We are lost no route found
    else
    {
        $handler->handle($serverrequest
                         ->withAttribute("success" , $this->success));
    }
    return $response ;
   }

/**
 * hasParams test if the path of the route has params
 * like ":id" return true  if has params else return false
 * @param  string $url [description]
 * @return bool        [description]
 */
   private function hasParams(string $url):bool
   {
       return  \preg_match('#:([\w]+)#',$url,$matches) ?true:false;
   }

    /**
     * @param string $name the name of the we want to generate an url from
     * @param array $param parameters needed to generate this url
     * @return string
     * @throw RouterException
     */
    public function generateUrl(string $scope, string $name, array $params=[])
   {
       $url = "test";
       //Does it need an isRouteExist($method,$scope,$name):bool  ?
       if (array_key_exists('GET',$this->routes) &&
           array_key_exists($scope,$this->routes['GET'] )&&
           array_key_exists($name,$this->routes['GET'][$scope])) {

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
           $url = '/'.$path;
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
