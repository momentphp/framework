<?php

namespace momentphp;

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
     * @param \Interop\Container\ContainerInterface $container
     * @param array $options
     */
    public function __construct(\Interop\Container\ContainerInterface $container, $options = [])
    {
        $this->container($container);
        $this->options($options);
    }

    /**
     * Invoke middleware
     *
     * @param  \Psr\Http\Message\RequestInterface $request
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @param  callable $next
     * @return \Psr\Http\Message\ResponseInterface
     */
    abstract public function __invoke($request, $response, $next);
}
