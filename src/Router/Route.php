<?php

namespace Router;


use Closure;
use Core\Traits\Hydrate;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Psr\Http\Message\RequestInterface;

class Route
{
	use Hydrate;

	private string $action = '';
    private ?Closure $callback = null;
	private string $controller = '';
	private string $method = '';
	private string $name = '';
	private array $params = [];
	private string $path;
    private string $pattern = '';
	private array $values = [];

    /**
	 * Route constructor.
	 * @param Closure|string|array $callback
	 */
	public function __construct(string $path = '', string $name = '', mixed $callback = '')
	{
        $this->path = $this->cleanPath($path);
        $this->parsePath();
        $this->compilePattern();

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

    private function parsePath()
    {
        preg_match_all("#/:(\w+)#", $this->path, $matches);
        foreach ($matches[1] as $match) {
            $this->params[$match] = Parameter::WORD;
        }

        return $this;
    }

    private function compilePattern(): static
    {
        $path = $this->getPath();
        $pattern = $path;
        foreach ($this->getParams() as $paramName => $expression) {
            $replacement = '(?<' . $paramName .'>' . $expression . ')';
            $pattern = preg_replace("#:(\w+)#", $replacement, $pattern, 1);
        }

        $this->pattern = $pattern;

        return $this;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

	public function getPath(): string
	{
		return $this->path;
	}


	public function setPath(string $path): Route
	{
		$this->path = $this->cleanPath($path);
        $this->parsePath();
        $this->compilePattern();
		return $this;
	}


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

	public function getParams(): array
	{
		return $this->params;
	}

	public function getValues(): array
	{
		return $this->values;
	}

	public function match(string $path): bool
	{
		$path = $this->cleanPath($path);
        $matched =  preg_match("#^$this->pattern$#", $path, $matches);
        if ($matched !== 1) {
            return false;
        }

        if (!$this->params) {
            return true;
        }

        foreach (array_keys($this->params) as $paramName) {
            $value = $matches[$paramName];
            $this->values[$paramName] = $value;
        }

        return true;
	}

    private function cleanPath($path): string
    {
        $path = trim($path, '/');
        $queryString = strpos($path, '?');

        if ($queryString) {
            $path = substr($path, 0, $queryString);
        }

        return $path;
    }

	private function hasParam(string $paramName): bool
	{
		return array_key_exists($paramName, $this->params);
	}

	public function with($paramName, $regex): static
	{
		$this->params[$paramName] = $regex;
        $this->compilePattern();
		return $this;
	}

	public function getCallback(): ?Closure
	{
		if (isset($this->callback)) {
		    return null;
		}
        return $this->callback;
	}

	public function getAction(): string
	{
		return $this->action;
	}

	public function getController(): string
	{
		return $this->controller;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function setCallback(Closure $callback): static
	{
		$this->callback = $callback;
		return $this;
	}

	public static function makeName(string $className, string $action): string
	{
        $factory = new InflectorFactory();
        $inflector = $factory->build();
		return $inflector->tableize($className . ucfirst($action));
	}

	private function getUrl(RequestInterface $request): string
	{
		$path = $request->getUri()->getPath();
		return trim($path, '/');
	}
}
