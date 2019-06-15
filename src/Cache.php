<?php

namespace momentphp;

/**
 * Cache
 */
class Cache
{
    /**
     * Cache manager
     *
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cacheManager;

    /**
     * Constructor
     *
     * @param Config $config
     * @param null|\Illuminate\Events\Dispatcher $eventsDispatcher
     */
    public function __construct(Config $config, \Illuminate\Events\Dispatcher $eventsDispatcher = null)
    {
        $container = new \Illuminate\Container\Container;
        $container['config'] = $config;
        $container->singleton('files', function () {
            return new \Illuminate\Filesystem\Filesystem;
        });
        $container->singleton('memcached.connector', function () {
            return new \Illuminate\Cache\MemcachedConnector;
        });
        $container->singleton('Illuminate\Contracts\Events\Dispatcher', function () use ($eventsDispatcher) {
            return $eventsDispatcher;
        });
        $this->cacheManager = new \Illuminate\Cache\CacheManager($container);
    }

    /**
     * Proxy calls to manager
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->cacheManager, $method], $args);
    }
}
