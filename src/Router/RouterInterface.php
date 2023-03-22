<?php

namespace Router;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    /**
     * @throws RouterException
     */
    public function match(ServerRequestInterface $request): Route;
}