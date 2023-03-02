<?php
namespace Router\Processor;

use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class Action implements ProcessorInterface
{
    private Response $response;
    private array $params;
    private $action;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function setParams(array $params): ProcessorInterface
    {
        $this->params = $params;
        return $this;
    }

    public function setCallable($callable): ProcessorInterface
    {
        $this->action = $callable;
        return $this;
    }

    public function execute(): ResponseInterface
    {
        if (class_exists($this->action)) {
            $action = new $this->action();
        } else {
            throw new Exception();
        }
        
        if (method_exists($action, 'handle')) {
            $result = call_user_func_array([$action, 'handle'], $this->params);
            $this->response->getBody()->write($result);
        } else {
            throw new Exception();
        }

        return $this->response;
    }
}
