<?php

namespace momentphp;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware
 */
abstract class Middleware
{
    use traits\ContainerTrait;
    use traits\OptionsTrait;
    use traits\ClassTrait;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param array $options
     */
    public function __construct(ContainerInterface $container, array $options = [])
    {
        $this->container($container);
        $this->options($options);
    }

    /**
     * Invoke middleware
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    abstract public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface;
}
