<?php

namespace momentphp;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

/**
 * Template
 */
class Template
{
    use traits\ContainerTrait;

    /**
     * Helpers registry
     *
     * @var Registry
     */
    public $registry;

    /**
     * Request
     *
     * @var RequestInterface
     */
    public $request;

    /**
     * Response
     *
     * @var ResponseInterface
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
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container($container);
        $this->registry = $this->prepareRegistry();
    }

    /**
     * Prepare helpers registry
     *
     * @return Registry
     */
    protected function prepareRegistry(): Registry
    {
        $registry = new Registry($this->container());
        $container = $this->container();
        $template = $this;
        $registry->factoryCallback(
            function ($class, $options) use ($container, $template) {
                return new $class($container, $template, $options);
            }
        );
        return $registry;
    }

    /**
     * Render a cell
     *
     * @return string
     */
    public function cell(): string
    {
        $args = func_get_args();
        $name = array_shift($args);
        $parts = explode(':', $name);
        if (count($parts) === 2) {
            [$name, $action] = [$parts[0], $parts[1]];
        } else {
            [$name, $action] = [$parts[0], 'display'];
        }
        $cell = $this->container()->get('registry')->load('cells\\' . $name . 'Controller');
        $response = $cell->{$action}($this->request->withAttribute('action', $action), new Response, $args);
        if ($response instanceof ResponseInterface) {
            $response = (string)$response->getBody();
        }
        return $response;
    }

    /**
     * Allow `d()` inside templates
     *
     * @param mixed $var
     * @return false|string
     */
    public function d($var)
    {
        return d($var, true);
    }

    /**
     * Return helper
     *
     * @param string $name
     * @return Helper|Registry
     */
    public function __get(string $name)
    {
        return $this->registry->helpers->{$name};
    }

    /**
     * Dynamic properties inside Twig templates
     *
     * @link   http://twig.sensiolabs.org/doc/recipes.html#using-dynamic-object-properties
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name)
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
