<?php

namespace momentphp\traits;

use momentphp\App;
use Psr\Container\ContainerInterface;

/**
 * ContainerTrait
 */
trait ContainerTrait
{
    /**
     * App
     *
     * @var App
     */
    public $app;

    /**
     * Container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Container getter/setter
     *
     * @param ContainerInterface|null $container
     * @return ContainerInterface|object
     */
    public function container(ContainerInterface $container = null)
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
