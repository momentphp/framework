<?php

namespace momentphp\providers;

/**
 * SmartyProvider
 */
class SmartyProvider extends \momentphp\Provider
{
    /**
     * Register service
     */
    public function __invoke()
    {
        $options = $this->options();
        if ($this->container()->has('debug')) {
            $options['debug'] = $this->container()->get('debug');
        }
        $this->container()->get('app')->service('smarty', function () use ($options) {
            return new \momentphp\SmartyViewEngine($options);
        });
    }
}
