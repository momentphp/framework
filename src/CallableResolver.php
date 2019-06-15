<?php

namespace momentphp;

/**
 * CallableResolver
 */
class CallableResolver implements \Slim\Interfaces\CallableResolverInterface
{
    use traits\ContainerTrait;

    /**
     * Base resolver
     *
     * @var \Slim\Interfaces\CallableResolverInterface
     */
    protected $baseResolver;

    /**
     * Constructor
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param array $options
     */
    public function __construct(\Interop\Container\ContainerInterface $container, \Slim\Interfaces\CallableResolverInterface $baseResolver)
    {
        $this->container($container);
        $this->baseResolver = $baseResolver;
    }

    /**
     * Resolve `$toResolve` into a callable
     *
     * @param  mixed $toResolve
     * @return callable
     */
    public function resolve($toResolve)
    {
        try {
            $resolved = $this->baseResolver->resolve($toResolve);
            return $resolved;
        } catch (\Exception $e) {
        }

        $toResolveArr = explode(':', $toResolve);
        $class = $toResolveArr[0];
        $method = isset($toResolveArr[1]) ? $toResolveArr[1] : null;

        $instance = $this->container()->get('registry')->load($class);
        return ($method) ? [$instance, $method] : $instance;
    }
}
