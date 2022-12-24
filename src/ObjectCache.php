<?php

namespace momentphp;

/**
 * ObjectCache
 */
class ObjectCache
{
    /**
     * Options
     *
     * @var array
     */
    protected $ocOptions = [
        'enabled' => true,
        'ttl' => 30,
        'ttlMap' => [],
        'cacheMethods' => [],
        'nonCacheMethods' => []
    ];

    /**
     * Initialize
     *
     * @param object $object
     * @param object $store
     * @param array $options
     */
    public function __invoke(object $object, object $store, array $options)
    {
        $this->ocOptions = array_replace_recursive($this->ocOptions, $options);
        $this->ocOptions['object'] = $object;
        $this->ocOptions['store'] = $store;
    }

    /**
     * Call and cache a class method
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Exception
     */
    public function ocCall(string $method, array $args = [])
    {
        $callback = [$this->ocOptions['object'], $method];
        $cache = $this->ocOptions['enabled'];
        if ($cache) {
            $cache = !in_array($method, $this->ocOptions['nonCacheMethods'], true);
        } else {
            $cache = in_array($method, $this->ocOptions['cacheMethods'], true);
        }
        if (!$cache) {
            if ($args) {
                return call_user_func_array($callback, $args);
            }
            return $this->ocOptions['object']->{$method}();
        }

        $key = $this->ocCacheKey($callback, $args);
        $ttl = $this->ocOptions['ttlMap'][$method] ?? $this->ocOptions['ttl'];

        $value = $this->ocOptions['store']->remember(
            $key,
            $ttl,
            function () use ($callback, $args) {
                if ($args) {
                    $ret = call_user_func_array($callback, $args);
                } else {
                    $ret = $callback();
                }
                return $ret;
            }
        );

        return $value;
    }

    /**
     * Return cache key
     *
     * @param callback $callback
     * @param array $args
     * @return string
     */
    protected function ocCacheKey(callable $callback, array $args = []): string
    {
        if (!method_exists($this->ocOptions['object'], 'objectKey')) {
            throw new \Exception('Object inside ObjectCache must implement objectKey() method');
        }
        $callbackKey = md5($this->ocOptions['object']->objectKey() . '::' . strtolower($callback[1]));
        $argumentKey = $this->ocArgumentsKey($args);
        return $callbackKey . $argumentKey;
    }

    /**
     * Return arguments key
     *
     * @param mixed $args
     * @return string
     */
    protected function ocArgumentsKey($args): string
    {
        if (!$args) {
            return '';
        }
        $serializedArgs = serialize(array_values($args));
        return md5($serializedArgs);
    }

    /**
     * Proxy calls to underlying object
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $method, array $args)
    {
        return $this->ocCall($method, $args);
    }
}
