<?php

namespace momentphp;

/**
 * Template
 */
class Template
{
    use traits\ContainerTrait;

    /**
     * Helpers registry
     *
     * @var \momentphp\Registry
     */
    public $registry;

    /**
     * Request
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    public $request;

    /**
     * Response
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    public $response;

    /**
     * Variables for the template
     *
     * @var array
     */
    public $vars = [];

    /**
     * Constructor
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(\Interop\Container\ContainerInterface $container)
    {
        $this->container($container);
        $this->registry = $this->prepareRegistry();
    }

    /**
     * Prepare helpers registry
     *
     * @return \momentphp\Registry
     */
    protected function prepareRegistry()
    {
        $registry = new Registry($this->container());
        $container = $this->container();
        $template = $this;
        $registry->factoryCallback(function ($class, $options) use ($container, $template) {
            return new $class($container, $template, $options);
        });
        return $registry;
    }

    /**
     * Render a cell
     *
     * @return string
     */
    public function cell()
    {
        $args = func_get_args();
        $name = array_shift($args);
        $parts = explode(':', $name);
        if (count($parts) === 2) {
            list($name, $action) = [$parts[0], $parts[1]];
        } else {
            list($name, $action) = [$parts[0], 'display'];
        }
        $cell = $this->container()->get('registry')->load('cells\\' . $name . 'Controller');
        $response = $cell->{$action}($this->request->withAttribute('action', $action), new \Slim\Http\Response, $args);
        if ($response instanceof \Psr\Http\Message\ResponseInterface) {
            $response = (string) $response->getBody();
        }
        return $response;
    }

    /**
     * Allow `d()` inside templates
     *
     * @param  mixed $var
     * @return string
     */
    public function d($var)
    {
        return d($var, true);
    }

    /**
     * Return helper
     *
     * @param  string $name
     * @return \momentphp\Helper|\momentphp\Registry
     */
    public function __get($name)
    {
        return $this->registry->helpers->{$name};
    }

    /**
     * Dynamic properties inside Twig templates
     *
     * @link   http://twig.sensiolabs.org/doc/recipes.html#using-dynamic-object-properties
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return true;
    }

    /**
     * Clone
     */
    public function __clone()
    {
        $this->registry = $this->prepareRegistry();
    }
}
