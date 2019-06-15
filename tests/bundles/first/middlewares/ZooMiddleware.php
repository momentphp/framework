<?php

namespace momentphp\tests\bundles\first\middlewares;

class ZooMiddleware extends \momentphp\Middleware
{
    public function __invoke($request, $response, $next)
    {
        return $next($request, $response->withHeader('X-Zoo', $this->options('animals')));
    }
}
