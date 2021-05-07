<?php

use Illuminate\Support\Str;
use momentphp\App;

if (!function_exists('app')) {
    /**
     * Return container or service within
     *
     * @param string|null $service
     * @return mixed
     */
    function app(string $service = null)
    {
        if ($service === null) {
            return App::getInstance();
        }
        return App::getInstance()->{$service};
    }
}

if (!function_exists('path')) {
    /**
     * Return filesystem path
     *
     * @param array $parts
     * @param string $ds
     * @return string
     */
    function path(array $parts = [], string $ds = DIRECTORY_SEPARATOR): string
    {
        return implode($ds, $parts);
    }
}

if (!function_exists('d')) {
    /**
     * Dump variable using `symfony/var-dumper`
     *
     * @param mixed $var
     * @param bool $return
     * @return false|string
     */
    function d($var, bool $return = false)
    {
        if ($return === true) {
            ob_start();
            dump($var);
            return ob_get_clean();
        }
        dump($var);
    }
}

if (!function_exists('class_namespace')) {
    /**
     * Return class namespace
     *
     * @param string $class
     * @param string|null $append
     * @return string
     * @throws ReflectionException
     */
    function class_namespace(string $class, string $append = null): string
    {
        $reflector = new \ReflectionClass($class);
        $namespaceName = $reflector->getNamespaceName();
        if ($append === null) {
            return $namespaceName;
        }
        return $namespaceName . '\\' . $append;
    }
}

if (!function_exists('class_path')) {
    /**
     * Return class filesystem path
     *
     * @param string $class
     * @param string|null $append
     * @return string
     * @throws ReflectionException
     */
    function class_path(string $class, string $append = null): string
    {
        $reflector = new \ReflectionClass($class);
        $basePath = dirname($reflector->getFileName());
        if ($append === null) {
            return $basePath;
        }
        return path([$basePath, $append]);
    }
}

if (!function_exists('class_prefix')) {
    /**
     * Return class prefix
     *
     * 'TestController' -> 'Test'
     * 'TestController' -> 'tests' (pluralize enabled)
     * 'Controller' -> ''
     *
     * @param string $class
     * @param boolean $pluralize
     * @return string
     */
    function class_prefix(string $class, bool $pluralize = false): string
    {
        return class_part($class, 'prefix', $pluralize);
    }
}

if (!function_exists('class_suffix')) {
    /**
     * Return class suffix
     *
     * 'TestController' -> 'Controller'
     * 'TestController' -> 'controllers' (pluralize enabled)
     * 'Controller' => 'Controller'
     *
     * @param string $class
     * @param boolean $pluralize
     * @return string
     */
    function class_suffix(string $class, bool $pluralize = false): string
    {
        return class_part($class, 'suffix', $pluralize);
    }
}

if (!function_exists('class_part')) {
    /**
     * Return class part
     *
     * @param string $class
     * @param string $type
     * @param boolean $pluralize
     * @return string
     */
    function class_part(string $class, string $type, bool $pluralize = false): string
    {
        $classBasename = class_basename($class);
        $classArr = explode('_', Str::snake($classBasename));
        $suffix = ucfirst(array_pop($classArr));
        $prefix = ucfirst(Str::camel(implode('_', $classArr)));
        $result = $$type;
        if ($pluralize) {
            $result = lcfirst(Str::plural($result));
        }
        return $result;
    }
}

if (!function_exists('class_config_key')) {
    /**
     * Return class config key
     *
     * @param string $class
     * @return string
     * @throws ReflectionException
     */
    function class_config_key(string $class): string
    {
        $namespaceArr = explode('\\', class_namespace($class));
        $found = false;
        do {
            $bundleClass = implode('\\', $namespaceArr) . '\\' . 'Bundle';
            if (class_exists($bundleClass)) {
                $found = true;
                break;
            }
            array_pop($namespaceArr);
        } while (!empty($namespaceArr));

        if ($found === false) {
            throw new \Exception('Unable to find bundle for class: ' . $class);
        }

        $bundleNamespace = implode('\\', $namespaceArr) . '\\';
        $namespace = substr($class, strlen($bundleNamespace));
        $namespaceArr = explode('\\', $namespace);
        $class = array_pop($namespaceArr);
        $namespaceArr[] = class_prefix($class);
        return implode('.', $namespaceArr);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable - supports boolean, empty and null
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        if (strlen($value) > 1 && Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
