<?php

namespace momentphp\tests\bundles\first\middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AnimalMiddleware extends \momentphp\Middleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        return $next($request, $response->withHeader('X-Animal', $this->options('type')));
    }
}
