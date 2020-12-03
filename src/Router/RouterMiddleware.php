<?php


namespace Router;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterMiddleware implements  MiddlewareInterface
{
    /**
     * @var Router
     */
    private Router $router;

    /**
     * RouterMiddleware constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }


    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $route = $this->router->match($request);
        if ($route) {
            $handler->handle($request
                ->withAttribute("success", true)
                ->withAttribute("route", $route)
            );
        } else {
            $handler->handle($request
                ->withAttribute("success", false));
        }

        return $response;
    }
}
