<?php

namespace Router;


use Closure;
use Core\Traits\Hydrate;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Psr\Http\Message\RequestInterface;

class Route
{
	use Hydrate;

	private string $name = '';
	private array $params = [];
	private string $path;
    private string $pattern = '';
	private array $values = [];
    private string $callableType = '';
    /**
     * @var mixed|string
     */
    private mixed $callable = null;
    private $extension  = '';

    /**
	 * Route constructor.
	 * @param Closure|string|array $callback
	 */
	public function __construct(string $path = '', string $name = '', mixed $callable = null)
	{
        $this->path = $this->cleanPath($path);
        $this->parsePath();
        $this->compilePattern();

		$this->name = $name;
       $this->setCallable($callable);
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
        preg_match("#\.(\w+)$#", $this->path, $parts);
        if ($parts) {
            $this->extension = $parts[1];
        }

        preg_match_all("#:(\w+)#", $this->path, $matches);
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

	public function hasParam(string $paramName): bool
	{
		return array_key_exists($paramName, $this->params);
	}

	public function with($paramName, $regex): static
	{
		$this->params[$paramName] = $regex;
        $this->compilePattern();
		return $this;
	}

    public function getCallable()
    {
        return $this->callable;
    }

    public function setCallable($callable)
    {
        $this->callable = $callable;
        if (is_string($callable)) {
            $this->callableType = 'action';
        }

        if (is_array($callable)) {
            $this->callableType = 'controller';
        }

        if ($callable instanceof Closure) {
            $this->callableType = 'callback';
        }

        return $this;
    }

    public function getCallableType(): string
    {
        return $this->callableType;
    }

    public function __invoke()
    {
        $result = call_user_func_array($this->getCallable(), $this->getValues());
        return $result;
    }


    public static function makeName(string $className, string $action): string
	{
        $factory = new InflectorFactory();
        $inflector = $factory->build();
		return $inflector->tableize($className . ucfirst($action));
	}

    public function getExtension()
    {
        return $this->extension;
    }

    private function getUrl(RequestInterface $request): string
	{
		$path = $request->getUri()->getPath();
		return trim($path, '/');
	}
}
