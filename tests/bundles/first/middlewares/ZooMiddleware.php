<?php

namespace momentphp\tests\bundles\first\middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ZooMiddleware extends \momentphp\Middleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        return $next($request, $response->withHeader('X-Zoo', $this->options('animals')));
    }
}
