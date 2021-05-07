<?php

namespace momentphp;

use Illuminate\Support\Str;
use Psr\Container\ContainerInterface;

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
     * @param ContainerInterface $container
     * @param array $options
     */
    public function __construct(ContainerInterface $container, array $options = [])
    {
        $this->container($container);
        $this->options($options);
    }

    /**
     * Return alias
     *
     * @return string
     */
    public function alias(): string
    {
        if ($this->options('alias')) {
            return $this->options('alias');
        }
        return strtolower(str_replace('\\', '.', static::classNamespace()));
    }

    /**
     * Return `true` if passed resource should be skipped from loading
     *
     * @param string $resource
     * @return bool
     */
    public function skipResource(string $resource): bool
    {
        $skip = $this->options('skipResource');
        if (!$skip) {
            return false;
        }
        $skip = (array)$skip;
        return in_array($resource, $skip, true);
    }

    /**
     * Return `true` if passed class should be skipped from loading
     *
     * @param string $class
     * @return bool
     */
    public function skipClass(string $class): bool
    {
        $skip = $this->options('skipClass');
        if (!$skip) {
            return false;
        }
        $skip = (array)$skip;
        foreach ($skip as $cl) {
            if (Str::startsWith($class, $cl)) {
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
    public function skipFingerprint(): bool
    {
        $skip = $this->options('skipFingerprint');
        if ($skip && ($skip === true)) {
            return true;
        }
        return false;
    }
}
