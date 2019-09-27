<?php
namespace xudid\Router;


use Psr\Http\Message\RequestInterface;

class Route {

    /**
     * @var $name string the route name
     */
    private $name;
    /**
     * @var $path string the ressource path
     */
    private $path;


    /**
     * @var array $params the route parameters
     */
    private $params = [];

    private $values = [];


    /**
     * @var callable |array a callable or an array with a class and a method name and a callback
     */
    private $callback;

    /**
     * Route constructor.
     * @param string $path
     * @param string $name
     * @param $callback
     */
    public function __construct(string $path, string $name,$callback)
    {
        $this->path = trim($path,'/');
        $this->name = $name;
        $this->callback = $callback;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }


    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function match(RequestInterface $request)
    {
        $path = $request->getUri()->getPath();
        $url = trim($path, '/');
        $pattern = "#^$this->path$#";
        $matched = false ;
        if (!empty($this->params)) {
            $matched = $this->matchWithParams($url);
            return $matched? true : false;
           }

        $matched = preg_match($pattern, $url, $matches);
        return $matched? true : false;

    }

    /**
     * @return string
     */
    private function matchWithParams($url)
    {

        preg_match_all("#/:(\w+)#", $this->path, $matches);
        $path = $this->path;
        $i=0;
        $params = $matches[1];
        $n = count($params);
        var_dump($url);
        var_dump($n);
        $matches = [];
        while($i < $n) {
            $paramName = $params[$i];
            var_dump($paramName);
            if (array_key_exists($paramName, $this->params)) {
               $path = preg_replace("#:(\w+)#", '('.$this->params[$paramName].')', $this->path);
               $matched = preg_match("#^$path$#", $url, $matches);
              if ($matched) {
                  array_shift($matches);
                  $this->values[$paramName] = $matches[$i];
              }

               var_dump($matches);
               var_dump($this->values);
           }

            $i++;
        }
        return $pattern = "#^$path$#";
    }

    /**
     * @param string $paramName
     * @return bool
     */
    private function hasParam(string $paramName) {
        return in_array($paramName,$this->params);
    }

    /**
     * @return $this
     */
    public function with($paramName, $regex)
    {
        $this->params[$paramName] = $regex;
        return $this;
    }

    /**
     *
     */
    public function getCallback()
    {
        return $this->callback;
    }
}