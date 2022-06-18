<?php

namespace Router;


use Closure;
use Core\Traits\Hydrate;
use Doctrine\Common\Inflector\Inflector;
use Psr\Http\Message\RequestInterface;

class Route
{
	use Hydrate;

	/**
	 * @var $name string the route name
	 */
	private string $name;
	/**
	 * @var $path string the resource path
	 */
	private string $path;

	/**
	 * @var array $params the route parameters
	 */
	private array $params = [];

	/**
	 * @var array $values parameters values when route match
	 */
	private array $values = [];

	/**
	 * @var Closure a callable or an array with a class and a method name and a callback
	 */
	private Closure $callback;
	private string $action = '';
	private string $controller = '';
	private string $method = '';

	/**
	 * Route constructor.
	 * @param string $path
	 * @param string $name
	 * @param Closure|string|array $callback
	 */
	public function __construct(string $path = '', string $name = '', mixed $callback = '')
	{
		$this->path = trim($path, '/');
		$this->name = $name;
		if (is_string($callback)) {
			$this->action = $callback;
		}

		if (is_array($callback)) {
			$this->controller = $callback[0];
			$this->method = $callback[1];
		}

		if ($callback instanceof Closure) {
			$this->callback = $callback;
		}
	}

	/**
	 * @return string route name
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return Route
	 */
	public function setName(string $name): Route
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * @param string $path
	 * @return Route
	 */
	public function setPath(string $path): Route
	{
		$this->path = trim($path, '/');
		return $this;
	}

	/**
	 * @param array $params
	 * @return Route
	 */
	public function setParams(array $params): Route
	{
		foreach ($params as $param) {
			$name = array_key_first($param);
			$expression = $param[$name];
			if (strlen($expression)) {
				$this->with($name, $expression);
			}
		}
		return $this;
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
	 * @method match
	 * @param string $path
	 * @return bool
	 */
	public function match(string $path): bool
	{
		$path = trim($path, '/');
		$queryString = strpos($path, '?');
		if ($queryString) {
			$path = substr($path, 0, $queryString);
		}
		$pattern = "#^$this->path$#";
		if (!empty($this->params)) {
			$pattern = $this->matchWithParams($path);
		}
		$matched = preg_match($pattern, $path, $matches);
		return (bool)$matched;
	}

	/**
	 * @param $urlPath
	 * @return string
	 */
	private function matchWithParams($urlPath): string
	{

		preg_match_all("#/:(\w+)#", $this->path, $matches);
		$path = $this->path;
		$i = 0;
		$params = $matches[1];
		$n = count($params);
		$matches = [];
		while ($i < $n) {
			$paramName = $params[$i];

			if (array_key_exists($paramName, $this->params)) {
				$path = preg_replace("#:(\w+)#", '(' . $this->params[$paramName] . ')', $this->path);
				$matched = preg_match("#^$path$#", $urlPath, $matches);
				if ($matched) {
					array_shift($matches);
					$this->values[$paramName] = $matches[$i];
				}
			}
			$i++;
		}
		return "#^$path$#";
	}

	/**
	 * @param string $paramName
	 * @return bool
	 */
	private function hasParam(string $paramName): bool
	{
		return in_array($paramName, $this->params);
	}

	/**
	 * @param $paramName
	 * @param $regex
	 * @return $this
	 */
	public function with($paramName, $regex)
	{
		$this->params[$paramName] = $regex;
		return $this;
	}


	public function getCallback(): ?Closure
	{
		if (isset($this->callback)) {
			return $this->callback;
		}
		return null;
	}

	/**
	 * @return string
	 */
	public function getAction(): string
	{
		return $this->action;
	}

	/**
	 * @return string
	 */
	public function getController(): string
	{
		return $this->controller;
	}

	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	 * @param Closure $callback
	 * @return Route
	 */
	public function setCallback(Closure $callback)
	{
		$this->callback = $callback;
		return $this;
	}

	/**
	 * Transform a ClassName in class_name
	 * @param string $className
	 * @param string $action
	 * @return string
	 */
	public static function makeName(string $className, string $action): string
	{
		return Inflector::tableize($className . ucfirst($action));
	}

	private function getUrl(RequestInterface $request): string
	{
		$path = $request->getUri()->getPath();
		return trim($path, '/');
	}
}
