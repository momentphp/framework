<?php

namespace momentphp;

use Monolog\Logger;

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
    public function __construct(array $options = [])
    {
        $this->options($options);
    }

    /**
     * Construct new logger
     *
     * @param string $name
     * @param array $options
     * @return Logger
     * @throws \ReflectionException
     */
    public function factory(string $name, array $options = []): Logger
    {
        $logger = new Logger($name);
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
     * @param string|null $name
     * @return Logger
     * @throws \ReflectionException
     */
    public function logger(string $name = null): Logger
    {
        $name = $name ?? $this->options('default');
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
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \ReflectionException
     */
    public function __call(string $method, array $args)
    {
        return call_user_func_array([$this->logger(), $method], $args);
    }
}
