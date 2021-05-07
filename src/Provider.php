<?php

namespace momentphp;

use Psr\Container\ContainerInterface;

/**
 * Provider
 */
abstract class Provider
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
     * Register services inside container
     */
    abstract public function __invoke();
}
