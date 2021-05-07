<?php

namespace momentphp;

use Psr\Container\ContainerInterface;
use Slim\Interfaces\CallableResolverInterface;

/**
 * CallableResolver
 */
class CallableResolver implements CallableResolverInterface
{
    use traits\ContainerTrait;

    /**
     * Base resolver
     *
     * @var CallableResolverInterface
     */
    protected $baseResolver;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param CallableResolverInterface $baseResolver
     */
    public function __construct(ContainerInterface $container, CallableResolverInterface $baseResolver)
    {
        $this->container($container);
        $this->baseResolver = $baseResolver;
    }

    /**
     * Resolve `$toResolve` into a callable
     *
     * @param string|callable $toResolve
     * @return callable
     */
    public function resolve($toResolve): callable
    {
        try {
            return $this->baseResolver->resolve($toResolve);
        } catch (\Exception $e) {
        }

        $toResolveArr = explode(':', $toResolve);
        $class = $toResolveArr[0];
        $method = $toResolveArr[1] ?? null;

        $instance = $this->container()->get('registry')->load($class);
        return ($method) ? [$instance, $method] : $instance;
    }
}
