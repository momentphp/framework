<?php

namespace momentphp;

/**
 * Log
 */
class Log
{
    use traits\CollectionTrait;
    use traits\OptionsTrait;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options($options);
    }

    /**
     * Construct new logger
     *
     * @param  string $name
     * @param  array $options
     * @return \Monolog\Logger
     */
    public function factory($name, $options = [])
    {
        $logger = new \Monolog\Logger($name);
        foreach ($options['handlers'] as $handler) {
            if (is_object($handler)) {
                $logger->pushHandler($handler);
            } else {
                $type = $handler['type'];
                $handlerClass = "Monolog\\Handler\\{$type}Handler";
                $reflect = new \ReflectionClass($handlerClass);
                $handler = $reflect->newInstanceArgs($handler['args']);
                $logger->pushHandler($handler);
            }
        }
        return $logger;
    }

    /**
     * Get logger by name
     *
     * @param  null|string $name
     * @return \Monolog\Logger
     */
    public function logger($name = null)
    {
        $name = ($name === null) ? $this->options('default') : $name;
        if (!$this->collection()->has($name)) {
            $options = $this->options('loggers.' . $name);
            if (!$options) {
                throw new \Exception('Undefined options for logger:' . $name);
            }
            $logger = $this->factory($name, $options);
            $this->collection()->put($name, $logger);
        }
        return $this->collection()->get($name);
    }

    /**
     * Proxy calls to the default logger
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->logger(), $method], $args);
    }
}
