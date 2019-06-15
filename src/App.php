<?php

namespace momentphp;

/**
 * App
 */
class App
{
    use traits\ContainerTrait;
    use traits\CollectionTrait;
    use traits\EventsDispatcherTrait;

    /**
     * Globally available app
     *
     * @var \momentphp\App
     */
    protected static $instance;

    /**
     * Slim app
     *
     * @var \Slim\App
     */
    public $slim;

    /**
     * Booted flag
     *
     * @var bool
     */
    public $booted = false;

    /**
     * Autoloader registered flag
     *
     * @var bool
     */
    protected $autoloaderRegistered = false;

    /**
     * Resources
     *
     * @var array
     */
    protected $resources = [];

    /**
     * Providers
     *
     * @var array
     */
    protected $providers = [];

    /**
     * Constructor
     *
     * @param array $bundles
     * @param array $container
     */
    public function __construct($bundles = [], $container = [])
    {
        $self = $this;
        static::setInstance($self);

        /**
         * App
         */
        if (!isset($container['app'])) {
            $container['app'] = function () use ($self) {
                return $self;
            };
        }

        /**
         * Registry
         */
        if (!isset($container['registry'])) {
            $container['registry'] = function ($c) {
                return new Registry($c);
            };
        }

        /**
         * Env
         */
        if (!isset($container['env'])) {
            $container['env'] = 'production';
        }

        /**
         * Debug
         */
        if (!isset($container['debug'])) {
            $container['debug'] = function ($c) {
                return $c->get('config')->get('app.displayErrorDetails', false);
            };
        }

        /**
         * Debug info
         */
        if (!isset($container['debugInfo'])) {
            $container['debugInfo'] = function ($c) {
                return new DebugInfo($c);
            };
        }

        /**
         * Console
         */
        if (!isset($container['console'])) {
            $container['console'] = function () {
                return (PHP_SAPI === 'cli');
            };
        }

        /**
         * Storage path
         */
        if (!isset($container['pathStorage'])) {
            $container['pathStorage'] = function ($c) {
                $path = $c->has('pathBase') ? [$c->get('pathBase'), 'storage'] : [sys_get_temp_dir(), 'momentphp'];
                return path($path);
            };
        }

        /**
         * Config
         */
        if (!isset($container['config'])) {
            $container['config'] = function ($c) {
                return new Config($c->get('app')->resourcePaths('config'), $c->get('env'));
            };
        }

        /**
         * Database
         */
        if (!isset($container['database'])) {
            $container['database'] = function ($c) {
                return new Database($c->get('config')->get('database', []), $c->get('debug'), $c->get('app')->eventsDispatcher());
            };
        }

        /**
         * View
         */
        if (!isset($container['view'])) {
            $container['view'] = function ($c) {
                $engine = $c->get('config')->get('app.viewEngine', 'twig');
                return new View($c, $engine);
            };
        }

        /**
         * Template
         */
        if (!isset($container['template'])) {
            $container['template'] = function ($c) {
                return new Template($c);
            };
        }

        /**
         * Flash messages
         */
        if (!isset($container['flash'])) {
            $container['flash'] = function ($c) {
                return new FlashMessages($c->get('view'));
            };
        }

        /**
         * Cache
         */
        if (!isset($container['cache'])) {
            $container['cache'] = function ($c) {
                return new Cache($c->get('config'), $c->get('app')->eventsDispatcher());
            };
        }

        /**
         * Object cache
         */
        if (!isset($container['objectCache'])) {
            $container['objectCache'] = function () {
                return new ObjectCache;
            };
        }

        /**
         * Log
         */
        if (!isset($container['log'])) {
            $container['log'] = function ($c) {
                return new Log($c->get('config')->get('loggers', []));
            };
        }

        /**
         * Error
         */
        if (!isset($container['error'])) {
            $container['error'] = function ($c) {
                return new Error($c);
            };
        }

        /**
         * Exception handler
         */
        if (!isset($container['exceptionHandler'])) {
            $container['exceptionHandler'] = function ($c) {
                return new ExceptionHandler($c);
            };
        }

        /**
         * Router (Slim)
         */
        if (!isset($container['router'])) {
            $container['router'] = function () {
                return new Router;
            };
        }

        /**
         * Callable resolver (Slim)
         */
        if (!isset($container['callableResolver'])) {
            $container['callableResolver'] = function ($c) {
                return new CallableResolver($c, new \Slim\CallableResolver($c));
            };
        }

        /**
         * Init Slim app
         */
        $this->slim = new \Slim\App($container);
        $this->container($this->slim->getContainer());

        /**
         * Settings (Slim)
         */
        $this->service('settings', function ($c) {
            return new Settings($c->get('config'), 'app');
        });

        /**
         * Add bundles
         */
        foreach ($bundles as $class => $options) {
            if (is_string($options)) {
                $class = $options;
                $options = [];
            }
            if (class_basename($class) !== 'Bundle') {
                throw new \Exception('Incorrect bundle class name: ' . $class);
            }
            $bundle = new $class($this->container(), $options);
            if ($this->bundles()->has($bundle->alias())) {
                throw new \Exception('Bundle already added: ' . $bundle->alias());
            }
            $this->bundles()->put($bundle->alias(), $bundle);
        }

        /**
         * Register autoloader
         */
        $this->registerAutoloader();

        /**
         * Add resources
         */
        $this->resource('routes', [$this, 'defaultPaths']);
        $this->resource('templates', [$this, 'defaultPaths']);
        $this->resource('config', [$this, 'defaultPaths']);

        /**
         * Register error handler
         */
        if ($this->container()->has('error')) {
            $this->container()->get('error')->register();
        }
    }

    /**
     * Default paths
     *
     * @param  \Illuminate\Support\Collection $bundles
     * @param  string $resource
     * @return array
     */
    protected function defaultPaths($bundles, $resource)
    {
        $paths = [];
        foreach ($bundles as $bundle) {
            if ($bundle->skipResource($resource)) {
                continue;
            }
            switch ($resource) {
                case 'config':
                    $paths[] = $bundle::classPath('config');
                    break;
                case 'routes':
                    array_unshift($paths, $bundle::classPath('routes.php'));
                    break;
                case 'templates':
                    $paths = [$bundle->alias() => $bundle::classPath('templates')] + $paths;
                    break;
            }
        }
        return $paths;
    }

    /**
     * Add resource
     *
     * @param string $key
     * @param callable $callable
     */
    public function resource($key, $callable)
    {
        $this->resources[$key] = $callable;
    }

    /**
     * Return resource paths
     *
     * @param  string $key
     * @return array
     */
    public function resourcePaths($key)
    {
        if (!isset($this->resources[$key])) {
            throw new \Exception('Undefined resource: ' . $key);
        }
        $callable = $this->resources[$key];
        return $callable($this->bundles(), $key);
    }

    /**
     * Get the globally available app
     *
     * @return \momentphp\App
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    /**
     * Set the globally available app
     *
     * @param \momentphp\App $app
     */
    public static function setInstance(App $app)
    {
        static::$instance = $app;
    }

    /**
     * Register autoloader
     *
     * @return bool
     */
    public function registerAutoloader()
    {
        if ($this->autoloaderRegistered) {
            return;
        }
        $this->autoloaderRegistered = true;
        return spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Unregister autoloader
     *
     * @return bool
     */
    public function unregisterAutoloader()
    {
        if (!$this->autoloaderRegistered) {
            return;
        }
        return spl_autoload_unregister([$this, 'loadClass']);
    }

    /**
     * Autoloader
     *
     * @param string $class
     */
    protected function loadClass($class)
    {
        if ($this->bundles()->isEmpty()) {
            return;
        }
        $namespace = $this->bundleClass();
        if (!\Illuminate\Support\Str::startsWith($class, $namespace)) {
            return;
        }
        $relativeClass = ltrim(substr($class, strlen($namespace)), '\\');
        foreach ($this->bundles()->reverse() as $bundle) {
            if ($bundle->skipClass($relativeClass)) {
                continue;
            }
            $fullClass = $bundle::classNamespace() . '\\' . $relativeClass;
            if (class_exists($fullClass)) {
                class_alias($fullClass, $class);
                return;
            }
        }
    }

    /**
     * Return PHP namespace of last bundle inside collection
     *
     * @param  null|string $class
     * @return string
     */
    public function bundleClass($relativeClass = null)
    {
        $last = $this->bundles()->last();
        $namespace = $last::classNamespace();
        if ($relativeClass === null) {
            return $namespace;
        }
        return $namespace . '\\' . $relativeClass;
    }

    /**
     * Register service in the container (helper)
     *
     * @param string $name
     * @param callable $callable
     */
    public function service($name, $callable)
    {
        $container = $this->container();
        $container[$name] = $callable;
    }

    /**
     * Build the path for a named route including the base path (helper)
     *
     * @param  string $name
     * @param  array $data
     * @param  array $queryParams
     * @return string
     */
    public function url($name, $data = [], $queryParams = [])
    {
        return $this->container()->get('router')->pathFor($name, $data, $queryParams);
    }

    /**
     * Return fingerprint
     *
     * @param  string $glue
     * @return string
     */
    public function fingerprint($glue = '_')
    {
        $aliases = [];
        foreach ($this->bundles() as $bundle) {
            if ($bundle->skipFingerprint()) {
                continue;
            }
            $aliases[] = $bundle->alias();
        }
        return implode($glue, $aliases);
    }

    /**
     * Return bundle(s)
     *
     * @param  null|string $alias
     * @return \Illuminate\Support\Collection|\momentphp\Bundle
     */
    public function bundles($alias = null)
    {
        return ($alias === null) ? $this->collection() : $this->collection()->get($alias);
    }

    /**
     * Register providers
     */
    public function registerProviders()
    {
        if (!$this->container()->has('config')) {
            return;
        }
        foreach ($this->container()->get('config')->get('app.providers', []) as $key => $name) {
            if ($name === false) {
                continue;
            }
            $provider = $this->container()->get('registry')->load($name);
            $provider();
            $this->providers[] = $provider;
        }
    }

    /**
     * Register middlewares
     */
    public function registerMiddlewares()
    {
        if (!$this->container()->has('config')) {
            return;
        }
        $self = $this;
        $middlewares = $this->container()->get('config')->get('app.middlewares', []);
        $load = function ($middlewares, $type) use ($self) {
            foreach ($middlewares as $key => $name) {
                if (($name === false) && ($type === 'app')) {
                    continue;
                }
                if (($name === false) && ($type === 'route')) {
                    $name = function ($request, $response, $next) {
                        return $next($request, $response);
                    };
                }
                $self->service($key, function () use ($self, $name) {
                    return $self->container()->get('registry')->load($name);
                });
                if ($type === 'app') {
                    $self->add($key);
                }
            }
        };
        if (isset($middlewares['app'])) {
            $load($middlewares['app'], 'app');
        }
        if (isset($middlewares['route'])) {
            $load($middlewares['route'], 'route');
        }
    }

    /**
     * Register routes
     */
    public function registerRoutes()
    {
        $app = $this;
        foreach ($this->resourcePaths('routes') as $path) {
            if (file_exists($path)) {
                include $path;
            }
        }
    }

    /**
     * Boot bundles
     */
    public function bootBundles()
    {
        foreach ($this->bundles() as $bundle) {
            if (method_exists($bundle, 'boot')) {
                $bundle->boot();
            }
        }
    }

    /**
     * Boot providers
     */
    public function bootProviders()
    {
        foreach ($this->providers as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
    }

    /**
     * Boot app
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        if ($this->container()->has('config')) {
            $config = $this->container()->get('config');
            date_default_timezone_set($config->get('app.timezone', 'UTC'));
            if (function_exists('locale_set_default')) {
                locale_set_default($config->get('app.locale', 'en'));
            }
            if (function_exists('mb_internal_encoding')) {
                mb_internal_encoding($config->get('app.encoding', 'UTF-8'));
            }
        }

        $this->registerProviders();
        $this->registerMiddlewares();
        $this->registerRoutes();

        $this->bootBundles();
        $this->bootProviders();

        $this->booted = true;
    }

    /**
     * Run app
     *
     * @param  bool $silent
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function run($silent = false)
    {
        if ($this->container->has('console') && $this->container->get('console')) {
            global $argv;
            $uri = (isset($argv[1])) ? '/' . $argv[1] : '/';
            $response = $this->visit($uri);
            $body = (string) $response->getBody();
            if (!empty($body)) {
                echo \Html2Text\Html2Text::convert($body);
            }
        } else {
            $this->boot();
            return $this->slim->run($silent);
        }
    }

    /**
     * Simulate app request
     *
     * @param  string $uri
     * @param  string $method
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function visit($uri, $method = 'GET')
    {
        $env = \Slim\Http\Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => $uri,
            'REQUEST_METHOD' => $method,
        ]);
        $uri = \Slim\Http\Uri::createFromEnvironment($env);
        $headers = \Slim\Http\Headers::createFromEnvironment($env);
        $cookies = [];
        $serverParams = $env->all();
        $body = new \Slim\Http\RequestBody;

        $request = new \Slim\Http\Request($method, $uri, $headers, $cookies, $serverParams, $body);
        $response = new \Slim\Http\Response;

        $this->boot();
        return $this->slim->process($request, $response);
    }

    /**
     * Container proxy method
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->container()->get($name);
    }

    /**
     * Container proxy method
     *
     * @param  string $name
     * @return mixed
     */
    public function __isset($name)
    {
        return $this->container()->has($name);
    }

    /**
     * Proxy calls to underlying Slim app
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args = [])
    {
        return call_user_func_array([$this->slim, $method], $args);
    }
}
