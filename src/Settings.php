<?php

namespace momentphp;

/**
 * Settings
 */
class Settings implements \ArrayAccess
{
    /**
     * Config
     *
     * @var \momentphp\Config
     */
    protected $config;

    /**
     * Config prefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * Constructor
     *
     * @param Config $config
     */
    public function __construct(Config $config, $prefix)
    {
        $this->config = $config;
        $this->prefix = $prefix;
    }

    /**
     * Return prefixed key
     *
     * @param  string $key
     * @return string
     */
    protected function key($key)
    {
        return $this->prefix . '.' . $key;
    }

    /**
     * Determine if the given configuration option exists
     *
     * @param  string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->config->has($this->key($key));
    }

    /**
     * Get a configuration option
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->config->get($this->key($key));
    }

    /**
     * Set a configuration option
     *
     * @param string $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        $this->config->set($this->key($key), $value);
    }

    /**
     * Unset a configuration option
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        $this->config->set($this->key($key), null);
    }
}
