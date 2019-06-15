<?php

namespace momentphp\tests\bundles\first\middlewares;

class AnimalMiddleware extends \momentphp\Middleware
{
    public function __invoke($request, $response, $next)
    {
        return $next($request, $response->withHeader('X-Animal', $this->options('type')));
    }
}
