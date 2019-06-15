<?php

namespace momentphp\traits;

/**
 * ContainerTrait
 */
trait ContainerTrait
{
    /**
     * App
     *
     * @var \momentphp\App
     */
    public $app;

    /**
     * Container
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * Container getter/setter
     *
     * @param  ContainerInterface|null $container
     * @return ContainerInterface|object
     */
    public function container(\Interop\Container\ContainerInterface $container = null)
    {
        if ($container !== null) {
            $this->container = $container;
            if ($container->has('app')) {
                $this->app = $container->get('app');
            }
            return $this;
        }
        return $this->container;
    }
}
