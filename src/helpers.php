<?php

if (!function_exists('app')) {
    /**
     * Return container or service within
     *
     * @param  null|string $service
     * @return mixed
     */
    function app($service = null)
    {
        if ($service === null) {
            return momentphp\App::getInstance();
        }
        return momentphp\App::getInstance()->{$service};
    }
}

if (!function_exists('path')) {
    /**
     * Return filesystem path
     *
     * @param  array $parts
     * @param  string $ds
     * @return string
     */
    function path($parts = [], $ds = DIRECTORY_SEPARATOR)
    {
        return implode($ds, $parts);
    }
}

if (!function_exists('d')) {
    /**
     * Dump variable using `symfony/var-dumper`
     *
     * @param  mixed $var
     * @param  bool $return
     * @return void|string
     */
    function d($var, $return = false)
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
     * @param  string $class
     * @param  null|string $append
     * @return string
     */
    function class_namespace($class, $append = null)
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
     * @param  string $class
     * @param  null|string $append
     * @return string
     */
    function class_path($class, $append = null)
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
     * @param  string $class
     * @param  boolean $pluralize
     * @return string
     */
    function class_prefix($class, $pluralize = false)
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
     * @param  string $class
     * @param  boolean $pluralize
     * @return string
     */
    function class_suffix($class, $pluralize = false)
    {
        return class_part($class, 'suffix', $pluralize);
    }
}

if (!function_exists('class_part')) {
    /**
     * Return class part
     *
     * @param  string $class
     * @param  string $type
     * @param  boolean $pluralize
     * @return string
     */
    function class_part($class, $type, $pluralize = false)
    {
        $classBasename = class_basename($class);
        $classArr = explode('_', \Illuminate\Support\Str::snake($classBasename));
        $suffix = ucfirst(array_pop($classArr));
        $prefix = ucfirst(\Illuminate\Support\Str::camel(implode('_', $classArr)));
        $result = $$type;
        if ($pluralize) {
            $result = lcfirst(\Illuminate\Support\Str::plural($result));
        }
        return $result;
    }
}

if (!function_exists('class_config_key')) {
    /**
     * Return class config key
     *
     * @param  string $class
     * @return string
     */
    function class_config_key($class)
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
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
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

        if (strlen($value) > 1 && \Illuminate\Support\Str::startsWith($value, '"') && \Illuminate\Support\Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
