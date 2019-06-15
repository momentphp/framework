<?php

namespace momentphp\providers;

/**
 * TwigProvider
 */
class TwigProvider extends \momentphp\Provider
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
        $this->container()->get('app')->service('twig', function () use ($options) {
            return new \momentphp\TwigViewEngine($options);
        });
    }
}
