<?php

namespace momentphp;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;

/**
 * Cache
 */
class Cache
{
    /**
     * Cache manager
     *
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * Constructor
     *
     * @param Config $config
     * @param null|Dispatcher $eventsDispatcher
     */
    public function __construct(Config $config, ?Dispatcher $eventsDispatcher = null)
    {
        $container = new Container;
        $container['config'] = $config;
        $container->singleton(
            'files',
            function () {
                return new Filesystem;
            }
        );
        $container->singleton(
            'memcached.connector',
            function () {
                return new MemcachedConnector;
            }
        );
        $container->singleton(
            Dispatcher::class,
            function () use ($eventsDispatcher) {
                return $eventsDispatcher;
            }
        );
        $this->cacheManager = new CacheManager($container);
    }

    /**
     * Proxy calls to manager
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return call_user_func_array([$this->cacheManager, $method], $args);
    }
}
