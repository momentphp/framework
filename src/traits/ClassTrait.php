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
     * @param string|null $append
     * @return string
     * @throws \ReflectionException
     */
    public static function classNamespace(?string $append = null): string
    {
        return class_namespace(static::class, $append);
    }

    /**
     * Proxy call to `class_path()`
     *
     * @param string|null $append
     * @return string
     * @throws \ReflectionException
     */
    public static function classPath(string $append = null): string
    {
        return class_path(static::class, $append);
    }

    /**
     * Proxy call to `class_prefix()`
     *
     * @param bool $pluralize
     * @return string
     */
    public static function classPrefix(bool $pluralize = false): string
    {
        return class_prefix(static::class, $pluralize);
    }

    /**
     * Proxy call to `class_suffix()`
     *
     * @param bool $pluralize
     * @return string
     */
    public static function classSuffix(bool $pluralize = false): string
    {
        return class_suffix(static::class, $pluralize);
    }

    /**
     * Proxy call to `class_part()`
     *
     * @param string $type
     * @param bool $pluralize
     * @return string
     */
    public static function classPart(string $type, bool $pluralize = false): string
    {
        return class_part(static::class, $type, $pluralize);
    }

    /**
     * Proxy call to `class_config_key()`
     *
     * @return string
     * @throws \ReflectionException
     */
    public static function classConfigKey(): string
    {
        return class_config_key(static::class);
    }

    /**
     * Proxy call to `class_basename()`
     *
     * @return string
     */
    public static function classBasename(): string
    {
        return class_basename(static::class);
    }
}
