<?php

namespace momentphp;

use Psr\Container\ContainerInterface;

/**
 * DebugInfo
 */
class DebugInfo
{
    use traits\ContainerTrait;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container($container);
    }

    /**
     * Return app debug info
     *
     * @param string|null $key
     * @return array
     */
    public function get(?string $key = null): array
    {
        $info = [];

        if ($this->container()->has('database')) {
            $info['database'] = [
                'title' => '$app&rarr;database&rarr;connection()',
                'description' => 'Default database connection (`false` if unable to connect)',
                'value' => $this->container()->get('database')->connectionStatus(),
            ];
        }

        if ($this->container()->has('env')) {
            $info['env'] = [
                'title' => '$app&rarr;env',
                'description' => 'Environment',
                'value' => $this->container()->get('env'),
            ];
        }

        if ($this->container()->has('environment')) {
            $info['environment'] = [
                'title' => '$app&rarr;environment',
                'description' => 'Server environment',
                'value' => $this->container()->get('environment'),
            ];
        }

        if ($this->container()->has('debug')) {
            $info['debug'] = [
                'title' => '$app&rarr;debug',
                'description' => 'Debug flag',
                'value' => $this->container()->get('debug'),
            ];
        }

        if ($this->container()->has('console')) {
            $info['console'] = [
                'title' => '$app&rarr;console',
                'description' => 'Console flag (`true` in CLI)',
                'value' => $this->container()->get('console'),
            ];
        }

        if ($this->container()->has('pathBase')) {
            $info['pathBase'] = [
                'title' => '$app&rarr;pathBase',
                'description' => 'Skeleton installation path',
                'value' => $this->container()->get('pathBase'),
            ];
        }

        if ($this->container()->has('pathWeb')) {
            $info['pathWeb'] = [
                'title' => '$app&rarr;pathWeb',
                'description' => 'Web server document root path',
                'value' => $this->container()->get('pathWeb'),
            ];
        }

        if ($this->container()->has('pathStorage')) {
            $info['pathStorage'] = [
                'title' => '$app&rarr;pathStorage',
                'description' => 'Storage path',
                'value' => $this->container()->get('pathStorage'),
            ];
        }

        if ($this->container()->has('config')) {
            $info['config'] = [
                'title' => '$app&rarr;config&rarr;getItems()',
                'description' => 'Configuration',
                'value' => $this->getConfig(),
            ];
        }

        if ($this->container()->has('registry')) {
            $info['registry'] = [
                'title' => '$app&rarr;registry&rarr;collection()&rarr;toArray()',
                'description' => 'Registry',
                'value' => $this->container()->get('registry')->collection()->toArray(),
            ];
        }

        $info['bundles'] = [
            'title' => '$app&rarr;bundles()&rarr;toArray()',
            'description' => 'Loaded bundles',
            'value' => $this->container()->get('app')->bundles()->toArray(),
        ];

        $info['services'] = [
            'title' => '$app&rarr;getContainer()&rarr;keys()',
            'description' => 'Services inside service container',
            'value' => $this->container()->keys(),
        ];

        return ($key) ? $info[$key] : $info;
    }

    /**
     * Return whole configuration
     *
     * @return array
     */
    protected function getConfig(): array
    {
        $c = $this->container()->get('config');
        foreach ($c->files() as $file) {
            $c->has($file);
        }
        return $c->getItems();
    }
}
