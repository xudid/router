<?php

namespace Illusion;

class Illusion
{
    private string $head = '';
    private string $body = '';
    private string $className = '';
    private array $methods = [];
    private array $uses = [];
    private $extendedClass = '';

    public static function withClass(string $name)
    {
        return new static($name);
    }

    public function __construct(string $name)
    {
        $this->className = $name . bin2hex(random_bytes(5));
    }

    public function use($use)
    {
        $this->uses[] = $use;
        return $this;
    }

    public function extends($class)
    {
        $this->extendedClass = $class;
        return $this;
    }
    public function withMethod(string $name, $body, $params = [])
    {
        $this->methods[$name] = ['body' => $body, 'params' => $params];
        return $this;
    }

    public function project()
    {
        foreach ($this->uses as $use) {
            $this->head .= 'use ' . $use . ';' .PHP_EOL ;
        }
        $this->head .= 'class ' . $this->className;
        if ($this->extendedClass) {
            $this->head .= ' extends ' . $this->extendedClass;
        }
        $this->head .= ' {' . PHP_EOL;
        $this->head .= PHP_EOL;

        $this->head .= PHP_EOL;

        $this->body = '';
        foreach ($this->methods as $name => $method) {
            $this->body .= ' public function ' . $name . ' (';
            $this->body .= implode(', ', $method['params'] ?? []) . ') {' . PHP_EOL;
            $this->body .= "\t\t" . $method['body'] ?? '' . PHP_EOL;
            $this->body .=  '}';


        }
        $this->body .= PHP_EOL . '}';
        eval($this->head . $this->body);
        return $this->className;
    }
}
