<?php

namespace momentphp\providers;

use momentphp\Provider;
use momentphp\TwigViewEngine;
use Twig\Error\LoaderError;

/**
 * TwigProvider
 */
class TwigProvider extends Provider
{
    /**
     * Register service
     * @throws LoaderError
     */
    public function __invoke()
    {
        $options = $this->options();
        if ($this->container()->has('debug')) {
            $options['debug'] = $this->container()->get('debug');
        }
        $this->container()->get('app')->service(
            'twig',
            function () use ($options) {
                return new TwigViewEngine($options);
            }
        );
    }
}
