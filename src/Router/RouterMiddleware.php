<?php


namespace Router;


use Exception;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouterMiddleware implements  MiddlewareInterface
{
    private RouterInterface $router;
    private string $routeKey = 'route';
    private string $successKey = 'success';

    public function __construct(RouterInterface $router, $routeKey = '', $successKey = '')
    {
        $this->router = $router;

        if ($routeKey) {
            $this->routeKey = $routeKey;
        }

        if ($successKey) {
            $this->successKey = $successKey;
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->router->match($request);
        if ($route->getCallable() == null) {
            $request = $request->withAttribute($this->successKey, false);
        } else {
            $request = $request->withAttribute($this->successKey, true)->withAttribute('route', $route);
        }

        $response = $handler->handle($request);
        return $response;
    }
}
