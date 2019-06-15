<?php

namespace momentphp\traits;

/**
 * ClassTrait
 */
trait ClassTrait
{
    /**
     * Proxy call to `class_namespace()`
     *
     * @param  string $append
     * @return string
     */
    public static function classNamespace($append = null)
    {
        return class_namespace(static::class, $append);
    }

    /**
     * Proxy call to `class_path()`
     *
     * @param  string $append
     * @return string
     */
    public static function classPath($append = null)
    {
        return class_path(static::class, $append);
    }

    /**
     * Proxy call to `class_prefix()`
     *
     * @param  bool $pluralize
     * @return string
     */
    public static function classPrefix($pluralize = false)
    {
        return class_prefix(static::class, $pluralize);
    }

    /**
     * Proxy call to `class_suffix()`
     *
     * @param  bool $pluralize
     * @return string
     */
    public static function classSuffix($pluralize = false)
    {
        return class_suffix(static::class, $pluralize);
    }

    /**
     * Proxy call to `class_part()`
     *
     * @param  string $type
     * @param  bool $pluralize
     * @return string
     */
    public static function classPart($type, $pluralize = false)
    {
        return class_part(static::class, $type, $pluralize);
    }

    /**
     * Proxy call to `class_config_key()`
     *
     * @return string
     */
    public static function classConfigKey()
    {
        return class_config_key(static::class);
    }

    /**
     * Proxy call to `class_basename()`
     *
     * @return string
     */
    public static function classBasename()
    {
        return class_basename(static::class);
    }
}
