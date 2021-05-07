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
     * @var Config
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
     * @param string $prefix
     */
    public function __construct(Config $config, string $prefix)
    {
        $this->config = $config;
        $this->prefix = $prefix;
    }

    /**
     * Return prefixed key
     *
     * @param string $key
     * @return string
     */
    protected function key(string $key): string
    {
        return $this->prefix . '.' . $key;
    }

    /**
     * Determine if the given configuration option exists
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->config->has($this->key($offset));
    }

    /**
     * Get a configuration option
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->config->get($this->key($offset));
    }

    /**
     * Set a configuration option
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->config->set($this->key($offset), $value);
    }

    /**
     * Unset a configuration option
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->config->set($this->key($offset), null);
    }
}
