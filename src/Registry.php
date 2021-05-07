<?php

namespace momentphp;

use Illuminate\Support\Str;
use Psr\Container\ContainerInterface;

/**
 * Registry
 */
class Registry
{
    use traits\ContainerTrait;
    use traits\CollectionTrait;

    /**
     * Path
     *
     * @var array
     */
    protected $path = [];

    /**
     * Factory callback
     *
     * @var callable
     */
    protected $factoryCallback;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container($container);
        $this->factoryCallback(
            function ($class, $options) use ($container) {
                $instance = new $class($container, $options);

                if ($container->has('cache') && $container->has('objectCache') && isset($options['cache']) && is_array($options['cache'])) {
                    $cacheOptions = $options['cache'];
                    $store = $container->get('cache');
                    if (isset($cacheOptions['store'])) {
                        $store = $store->store($cacheOptions['store']);
                        unset($cacheOptions['store']);
                    }
                    $nonCacheMethods = ['options'];
                    if (isset($cacheOptions['nonCacheMethods'])) {
                        foreach ($cacheOptions['nonCacheMethods'] as $method) {
                            if (!in_array($method, $nonCacheMethods, true)) {
                                $nonCacheMethods[] = $method;
                            }
                        }
                    }
                    $cacheOptions['nonCacheMethods'] = $nonCacheMethods;
                    $objectCache = clone $container->get('objectCache');
                    $objectCache($instance, $store, $cacheOptions);
                    return $objectCache;
                }

                return $instance;
            }
        );
    }

    /**
     * Loads/constructs an object instance
     *
     * @param string $name
     * @return object
     * @throws \Exception
     */
    public function load(string $name): object
    {
        $key = $this->prefix($name);
        if ($this->collection()->has($key)) {
            return $this->collection()->get($key);
        }
        if ($this->container()->has($key)) {
            $instance = $this->container()->get($key);
        } else {
            $instance = $this->factory($name, $this->options($name));
        }
        $this->collection()->put($key, $instance);
        return $instance;
    }

    /**
     * Return new instance
     *
     * @param string $name
     * @param array $options
     * @return object
     * @throws \Exception
     */
    public function factory(string $name, array $options = []): object
    {
        $key = $this->prefix($name);
        $class = $this->container()->get('app')->bundleClass($key);
        if (!class_exists($class)) {
            throw new \Exception('Class not found: ' . $class);
        }
        $factoryCallback = $this->factoryCallback();
        return $factoryCallback($class, $options);
    }

    /**
     * Factory callback getter/setter
     *
     * @param callable|null $callback
     * @return $this|\Closure
     */
    public function factoryCallback(callable $callback = null)
    {
        if ($callback !== null) {
            $this->factoryCallback = $callback;
            return $this;
        }
        return $this->factoryCallback;
    }

    /**
     * Return options from configuration
     *
     * @param string $name
     * @return array
     * @throws \Exception
     */
    public function options(string $name): array
    {
        $options = [];
        if ($this->container()->has('config')) {
            $key = $this->prefix($name);
            $class = $this->container()->get('app')->bundleClass($key);
            if (!class_exists($class)) {
                throw new \Exception('Class not found: ' . $class);
            }
            $options = $this->container()->get('config')->get($class::classConfigKey(), []);
        }
        return $options;
    }

    /**
     * Add prefix
     *
     * 'PostModel' -> 'models\PostModel'
     *
     * @param string $name
     * @return string
     */
    protected function prefix(string $name): string
    {
        $prefix = class_suffix($name, true);
        return $prefix . '\\' . $name;
    }

    /**
     * Loads/constructs an object instance
     *
     * @param string $name
     * @return object|$this
     */
    public function __get(string $name)
    {
        $this->path[] = $name;
        if (count($this->path) > 1) {
            $suffix = ucfirst(Str::singular($this->path[0]));
            $name = implode('\\', array_slice($this->path, 1)) . $suffix;
            try {
                $instance = $this->load($name);
                $this->path = [];
                return $instance;
            } catch (\Exception $e) {
            }
        }
        return $this;
    }

    /**
     * Dynamic properties inside Twig templates
     *
     * @link   http://twig.sensiolabs.org/doc/recipes.html#using-dynamic-object-properties
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name)
    {
        return true;
    }
}
