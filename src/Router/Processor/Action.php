<?php
namespace Router\Processor;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Xudid\Container\Weaver;

class Action extends AbstractProcessor
{
    private array $params;
    private $callable;
    private Weaver $weaver;

    public function __construct(Weaver $weaver, $callable, array $params = [])
    {
        $this->callable = $callable;
        $this->params = $params;
        $this->weaver = $weaver;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!class_exists($this->callable)) {
            throw new Exception();
        }

        $response = $handler->handle($request);
        $constructorArguments = [
            ServerRequestInterface::class => $request,
            ResponseInterface::class => $response,
        ];

        $action = $this->weaver->make($this->callable, $constructorArguments);
        if (!method_exists($action, 'handle')) {
            throw new Exception();
        }

        $result = call_user_func_array([$action, 'handle'], [$this->params]);
        $request = $request->withAttribute($this->resultKey, $result);
        $response = $handler->handle($request);

        return $response;
    }
}
