<?php
namespace Brick\Router;

 use GuzzleHttp\Psr7\ServerRequest;
 use Interop\Http\Server\MiddlewareInterface;
 use Psr\Http\Message\ServerRequestInterface;
 use Psr\Http\Message\ResponseInterface;
 use Interop\Http\Server\RequestHandlerInterface;
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
  /**
   *
   */
  function __construct()
  {

  }
  /**
   * Register a Route to a ressource asked with a HTTP GET method
   * @param string $scope the logic url domain without a '/'
   * @param string $path the actual url path to the ressource with beginning '/'
   * @param array|callable $callable callback function to call if route match
   * the array contains in order an object , the name of a function to call
   * on this object , the callback function to call after that
   * @param string|null $displayname string to display in menu
   * @param bool $returnview if it must return a view when route match
   * @return Route
   *
   */
  public function get(string $scope,
                      string $path,
                       $callable,
                      string $displayname=null,
                      $returnview=true):Route
 {

    $route = new \Brick\Router\Route($path,$callable,$displayname,$returnview);
    $this->routes['GET'][$scope][]=$route;

    return $route;

 }
/**
 * Register a Route to a ressource asked with a HTTP POST method
 * @param string $scope the logic url domain without a '/'
 * @param string $path the actual url path to the ressource with beginning '/'
 * @param array|callable $callable callback function to call if route match
 * the array contains in order an object , the name of a function to call
 * on this object , the callback function to call after that
 * @param string|null $displayname string to display in menu
 * @param bool $returnview if it must return a view when route match
 * @return Route
 *
 */
  public function post(string $scope,
                       string $path,
                        $callable,
                       string $displayname=null,
                       bool $returnview=true):Route
 {
   $route = new \Brick\Router\Route($path,$callable,$displayname,$returnview);
   $this->routes['POST'][$scope][]=$route;
   return $route;
 }

 /**
  * Return  an array with route in the scope except given path and
  * an array of allowed path
  * @param string $scope the logic url domain without a '/'
  * @param string $path the actual url path to the ressource without beginning '/'
  * @param string|null $id if we need an id to access the ressource
  * @param array $allowedlinks array of allowed ressource from the actual path
  * @return array
  *
  */
public function getUrlsMatrix(string $scope,string $path,string $id=null,array $allowedlinks)
 {
   $urls=[];
   if(array_key_exists($scope, $this->routes['POST']))
     {
       foreach ($this->routes['POST'][$scope] as $key => $value)
       {

       $cpath = $value->getPath();
       $display = $value->getDisplayName();
       $mcpath=$cpath;
       //Path has params and id is not null and
       if($this->hasParams($path)&&!is_null($id))
       {
         $mcpath = str_replace(":id", $id, $cpath );

         if(in_array($cpath, $allowedlinks)&&$cpath!=$path)
         {
           if($display !=""||$display!=null)
           {
             $urls["POST"][] = [$mcpath=>$value->getDisplayName()];
           }

         }
       }
       else
       {

         if(in_array($cpath, $allowedlinks)&&$cpath!=$path)
         {
           if(!$this->hasParams($cpath))
           {
             //$displayname = $value->getDisplayName();
             if($display !=""||$display!=null)
             {
               $urls["POST"][] = [$mcpath=>$value->getDisplayName()];
             }
           }


         }
       }

      }
    }

    if(array_key_exists($scope, $this->routes['GET']))
    {
    foreach ($this->routes['GET'][$scope] as $key => $value)
    {
      $cpath = $value->getPath();
      $display = $value->getDisplayName();
      $mcpath=$cpath;
      if($this->hasParams($path)&&!is_null($id))
      {
        $mcpath = str_replace(":id", $id, $cpath );

        if(in_array($cpath, $allowedlinks)&&$cpath!=$path)
        {
          $displayname = $value->getDisplayName();
          if($displayname !=""||$displayname!=null)
          {
            $urls["GET"][] = [$mcpath=>$display];
          }
        }
      }
      else
      {
        if(in_array($cpath, $allowedlinks)&&$cpath!=$path)
        {
          if(!$this->hasParams($cpath))
          {
            $displayname = $value->getDisplayName();
            if($displayname !=""||$displayname!=null)
            {
              $urls["GET"][] = [$mcpath=>$display];
            }
        }
        }
      }

    }
  }

    return $urls;
  }

/**
 * Run registred routes exploration
 * If a route match put success attribute at true on the request
 * Set the route attribut on the request and the returnAppPage to true
 * on it .
 * Else set success attribute at false on the request
 * Return both the handled response
 * @param ServerRequestInterface $serverrequest  request made by the user
 * @param RequestHandlerInterface $handler the fianl response handler
 * @return ResponseInterface
 */
  public function process(ServerRequestInterface $serverrequest,
                          RequestHandlerInterface $handler):ResponseInterface
  {
    $response = $handler->handle($serverrequest);
    $this->url = ($serverrequest->getUri()->getPath());
    $parts = \explode('/',$this->url);
    $scope = $parts[1];
    $method = $serverrequest->getMethod();

    //Enigmatic request
    if(!isset($this->routes[$method][$scope]))
    {
      $handler->handle($serverrequest
      ->withAttribute("success" , $this->success));
      return $response;
    }

    //Walk through the routes
    foreach ($this->routes[$method][$scope] as $route)
    {
        if($route->match($this->url))
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
      ->withAttribute("returnAppPage", $this->currentRoute->isReturnAppPage()));
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
     $hasparams = false;
     $match = \preg_match('#:([\w]+)#',$url,$matches);
     if($match === 1)
     {
       $hasparams = true;
     }
     return $hasparams ;
   }
}


?>
