<?php
namespace Brick\Router;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Http\Server\RequestHandlerInterface;

/**
*
*/
class Route
{
  /**
   * @var ResponseInterface $response
   */
   private $response;

  /**
   * @var string $path
   */
   private $path;

  /**
   * @var callable $callable
   */
   private $callable;

  /**
   * @var array $matches
   */
  private $matches= [];

  /**
   * @var array $params
   */
  private $params= [];

  /**
   * @var bool $hasparams : to test if Route has params or not
   *
   */
   private $hasparams=false;

  /**
   * @var string $controller : controller full class name
   */
   private $controller=null;

  /**
   * @var string $action : the action to call on the controller
   */
   private $action;

   /*
   * @var string $displayname : the name to display in link display
   */
   private $displayname ;

/**
 * [private description]
 * @var bool
 */
   private $returnview = true;

  /**
   * @param string $path
   * @param callable $callable
   * @param string|null $displayname the route $displayname for menus/links
   */
   function __construct($path,$callable, string$displayname=null,$returnview)
   {
     $this->path= trim($path,'/');
     $this->callable=$callable;
     $this->displayname = $displayname;
     $this->returnview = $returnview;
   }


  /**
   * @param string $url
   * @return bool
   */
   public function match($url):bool
   {

    $url = trim($url,'/');
    $path = \preg_replace_callback('#:([\w]+)#',[$this,'paramMatch'],$this->path);
    $regex = "#^$path$#i";

    //$regex ce a quoi doit correpondre l'url
    //$url l ' url
    //$matches les resultats du match
        if(!\preg_match($regex,$url,$matches))
        {
          return false;
        }

    array_shift($matches);

    $this->matches = $matches;
    return true;

  }
  /**
   * @param array $match
   */
   private function paramMatch($match)
   {
    //'([^/]+)'

    if(isset($this->params[$match[1]]))
    {
      return '('.$this->params[$match[1]].')';
    }
    return '([^/]+)';
   }
  /**
   * @param string $param
   * @param string $regex
   * @return self
   */
   public function with($param,$regex):self
   {
     $this->params[$param] = $regex;
     return $this;
   }

  /**
   * @return string controller or callable
   */
  public function getController()
  {
    if(\is_array($this->callable))
    {
      return $this->callable[0];
    }
    return $this->callable;
  }
/**
 * @return string action name to call on the controller or
 * an empty string
 */
  public function getAction():string
  {
    if(\is_array($this->callable))
    {
      return $this->callable[1];
    }
  return "";
  }
/**
 * @return array elements who matches in url
 */
  public function getMatches():array
  {
    return $this->matches;
  }

  /**
   * @return callable return the callable to call when an url matched
   */
  public function getCallback()
  {
    if(\is_array($this->callable))
    {
      return $this->callable[2];
    }
  }
  public function getDisplayName()
  {
    return $this->displayname;
  }

  public function getPath()
  {
    return $this->path;
  }

  public function withoutView()
  {
    $this->returnview = false;
  }

  public function isReturnAppPage()
  {
    return $this->returnview;
  }

}
