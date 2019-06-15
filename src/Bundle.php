<?php

namespace momentphp;

/**
 * Bundle
 */
abstract class Bundle
{
    use traits\ContainerTrait;
    use traits\OptionsTrait;
    use traits\ClassTrait;

    /**
     * Constructor
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param array $options
     */
    public function __construct(\Interop\Container\ContainerInterface $container, $options = [])
    {
        $this->container($container);
        $this->options($options);
    }

    /**
     * Return alias
     *
     * @return string
     */
    public function alias()
    {
        if ($this->options('alias')) {
            return $this->options('alias');
        }
        return strtolower(str_replace('\\', '.', static::classNamespace()));
    }

    /**
     * Return `true` if passed resource should be skipped from loading
     *
     * @param  string $resource
     * @return bool
     */
    public function skipResource($resource)
    {
        $skip = $this->options('skipResource');
        if (!$skip) {
            return false;
        }
        $skip = (array) $skip;
        return in_array($resource, $skip);
    }

    /**
     * Return `true` if passed class should be skipped from loading
     *
     * @param  string $class
     * @return bool
     */
    public function skipClass($class)
    {
        $skip = $this->options('skipClass');
        if (!$skip) {
            return false;
        }
        $skip = (array) $skip;
        foreach ($skip as $cl) {
            if (\Illuminate\Support\Str::startsWith($class, $cl)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return `true` if bundle should be skipped from fingerprint
     *
     * @return bool
     */
    public function skipFingerprint()
    {
        $skip = $this->options('skipFingerprint');
        if ($skip && ($skip === true)) {
            return true;
        }
        return false;
    }
}
