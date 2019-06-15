<?php

namespace momentphp;

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
     * @param \Interop\Container\ContainerInterface $container
     * @param array $options
     */
    public function __construct(\Interop\Container\ContainerInterface $container, $options = [])
    {
        $this->container($container);
        $this->options($options);
    }

    /**
     * Register services inside container
     */
    abstract public function __invoke();
}
