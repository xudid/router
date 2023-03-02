<?php


namespace Router;


use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterMiddleware implements  MiddlewareInterface
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }


    /**
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $route = $this->router->match($request);
        if ($route) {
            $request = $request->withAttribute("success", true)
                ->withAttribute("route", $route);
            $handler->handle($request);
        } else {
            $request = $request->withAttribute("success", false);
            $handler->handle($request);
        }

        return $response;
    }
}
