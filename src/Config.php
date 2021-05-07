<?php

namespace momentphp;

use Illuminate\Support\Arr;

/**
 * Config
 */
class Config implements \ArrayAccess
{
    /**
     * Current configuration environment
     *
     * @var string
     */
    protected $environment;

    /**
     * Directories to search config files for
     *
     * @var array
     */
    protected $configDirs = [];

    /**
     * Parsed configuration keys cache
     *
     * @var array
     */
    protected $parsed = [];

    /**
     * Existing groups cache
     *
     * @var array
     */
    protected $exists = [];

    /**
     * All of the configuration items
     *
     * @var array
     */
    protected $items = [];

    /**
     * Constructor
     *
     * @param array $configDirs
     * @param string $environment
     */
    public function __construct(array $configDirs, string $environment = 'production')
    {
        $this->configDirs = $configDirs;
        $this->environment = $environment;
    }

    /**
     * Get the specified configuration value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        [$group, $item] = $this->parseKey($key);
        $this->load($group);
        return Arr::get($this->items[$group], $item, $default);
    }

    /**
     * Set a given configuration value
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        list($group, $item) = $this->parseKey($key);
        $this->load($group);
        if ($item === null) {
            $this->items[$group] = $value;
        } else {
            Arr::set($this->items[$group], $item, $value);
        }
    }

    /**
     * Determine if the given configuration value exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $default = microtime(true);
        return $this->get($key, $default) !== $default;
    }

    /**
     * Determine if a configuration group exists
     *
     * @param string $key
     * @return bool
     */
    public function hasGroup(string $key): bool
    {
        [$group, $item] = $this->parseKey($key);
        return $this->exists($group);
    }

    /**
     * Get all of the configuration items
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get directories to search config files for
     *
     * @return array
     */
    public function getConfigDirs(): array
    {
        return $this->configDirs;
    }

    /**
     * Get current configuration environment
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Parse a key into group and item
     *
     * @param string $key
     * @return array
     */
    protected function parseKey(string $key): array
    {
        if (isset($this->parsed[$key])) {
            return $this->parsed[$key];
        }
        $segments = explode('.', $key);
        $parsed = $this->parseBasicSegments($segments);
        return $this->parsed[$key] = $parsed;
    }

    /**
     * Parse an array of basic segments
     *
     * @param array $segments
     * @return array
     */
    protected function parseBasicSegments(array $segments): array
    {
        $group = $segments[0];
        if (count($segments) === 1) {
            return [$group, null];
        }

        $item = implode('.', array_slice($segments, 1));
        return [$group, $item];
    }

    /**
     * Load the configuration group for the key
     *
     * @param string $group
     */
    protected function load(string $group): void
    {
        $env = $this->environment;
        if (isset($this->items[$group])) {
            return;
        }
        $items = $this->loadItems($env, $group);
        $this->items[$group] = $items;
    }

    /**
     * Load the given configuration group
     *
     * @param string $environment
     * @param string $group
     * @return array
     */
    protected function loadItems(string $environment, string $group): array
    {
        $items = [];
        foreach ($this->configDirs as $path) {
            $file = "{$path}/{$group}.php";
            if (file_exists($file)) {
                $items = $this->mergeEnvironment($items, $file);
            }
        }
        foreach ($this->configDirs as $path) {
            $file = "{$path}/{$environment}/{$group}.php";
            if (file_exists($file)) {
                $items = $this->mergeEnvironment($items, $file);
            }
        }
        return $items;
    }

    /**
     * Determine if the given group exists
     *
     * @param string $group
     * @return bool
     */
    public function exists(string $group): bool
    {
        $key = $group;
        if (isset($this->exists[$key])) {
            return $this->exists[$key];
        }
        $exists = false;
        foreach ($this->configDirs as $path) {
            $file = "{$path}/{$group}.php";
            if (file_exists($file)) {
                $exists = true;
                break;
            }
        }
        return $this->exists[$key] = $exists;
    }

    /**
     * Merge the items in the given file into the items
     *
     * @param array $items
     * @param string $file
     * @return array
     */
    protected function mergeEnvironment(array $items, string $file): array
    {
        return array_replace_recursive($items, $this->getRequire($file));
    }

    /**
     * Get the returned value of a file
     *
     * @param string $path
     * @return mixed
     * @throws \Exception
     */
    protected function getRequire(string $path)
    {
        if (is_file($path)) {
            return include $path;
        }
        throw new \Exception("File does not exist at path {$path}");
    }

    /**
     * Determine if the given configuration option exists
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get a configuration option
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set a configuration option
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Unset a configuration option
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }

    /**
     * Returns unique config file names across all config dirs
     *
     * @return array
     */
    public function files(): array
    {
        $files = [];
        foreach ($this->configDirs as $path) {
            if (!file_exists($path)) {
                continue;
            }
            $directory = new \RecursiveDirectoryIterator($path);
            $iterator = new \RecursiveIteratorIterator($directory);
            $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
            foreach ($regex as $o) {
                foreach ($o as $file) {
                    $file = basename($file, '.php');
                    if (!in_array($file, $files, true)) {
                        $files[] = $file;
                    }
                }
            }
        }
        return $files;
    }
}
