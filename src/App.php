<?php

declare(strict_types=1);

/*
 * This file is part of the `momentphp/framework` package.
 */

namespace momentphp;

use Pimple\Container as PimpleContainer;
use Pimple\Psr11\Container as Psr11Container;
use Psr\Container\ContainerInterface;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;

/**
 * @method ContainerInterface getContainer()
 */
class App
{
    protected SlimApp $slim;

    protected PimpleContainer $pimpleContainer;

    /**
     * @param array<string|int, string|array> $bundles
     * @param array<string, mixed>            $container
     */
    public function __construct(
        array $bundles = [],
        array $container = []
    ) {
        /*
         * Env
         */
        if (!isset($container['env'])) {
            $container['env'] = 'production';
        }

        /*
         * Console
         */
        if (!isset($container['console'])) {
            $container['console'] = function () {
                return PHP_SAPI === 'cli';
            };
        }

        $this->pimpleContainer = new PimpleContainer($container);
        $this->slim = AppFactory::createFromContainer(new Psr11Container(
            $this->pimpleContainer
        ));

        /*
         * Add bundles
         */
        foreach ($bundles as $class => $options) {
        }
    }

    /**
     * Register shared service (singleton) inside container (same instance is
     * returned for all calls).
     */
    public function service(string $name, callable $callable): void
    {
        $this->pimpleContainer[$name] = function (PimpleContainer $container) use ($callable) {
            return $callable(new Psr11Container($container));
        };
    }

    /**
     * Register non-shared service inside container (different instance is
     * returned for all calls).
     */
    public function serviceFactory(string $name, callable $callable): void
    {
        $this->pimpleContainer[$name] = $this->pimpleContainer->factory(
            function (PimpleContainer $container) use ($callable) {
                return $callable(new Psr11Container($container));
            }
        );
    }

    /**
     * Register anonymous function as a service. The function will be returned
     * without being invoked.
     */
    public function serviceProtect(string $name, callable $callable): void
    {
        $this->pimpleContainer[$name] = $this->pimpleContainer->protect($callable);
    }

    /**
     * Modify service after definition.
     */
    public function serviceExtend(string $name, callable $callable): void
    {
        $this->pimpleContainer->extend(
            $name,
            function ($service, PimpleContainer $container) use ($callable) {
                return $callable($service, new Psr11Container($container));
            }
        );
    }

    /**
     * Retrieve service from container via magic getter: `$app->service`.
     */
    public function __get(string $name): mixed
    {
        return $this->getContainer()->get($name);
    }

    /**
     * Check if service exists in container: `isset($app->service)`.
     */
    public function __isset(string $name): bool
    {
        return $this->getContainer()->has($name);
    }

    /**
     * Proxy calls to underlying Slim app.
     *
     * @param array<int, mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        // @phpstan-ignore-next-line
        return call_user_func_array([$this->slim, $name], $arguments);
    }
}
