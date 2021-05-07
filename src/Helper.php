<?php

namespace momentphp;

use Psr\Container\ContainerInterface;

/**
 * Helper
 */
abstract class Helper
{
    use traits\ContainerTrait;
    use traits\OptionsTrait;
    use traits\ClassTrait;

    /**
     * Template
     *
     * @var Template
     */
    protected $template;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param Template $template
     * @param array $options
     */
    public function __construct(ContainerInterface $container, Template $template, array $options = [])
    {
        $this->container($container);
        $this->template = $template;
        $this->options($options);
    }
}
