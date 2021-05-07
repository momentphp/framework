<?php

namespace momentphp;

use momentphp\interfaces\ViewEngineInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * View
 */
class View
{
    use traits\ContainerTrait;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param string $engine
     */
    public function __construct(ContainerInterface $container, string $engine)
    {
        $this->container($container);
        $this->engine = $engine;
    }

    /**
     * Guarded properties
     *
     * @var array
     */
    protected $guarded = [
        'viewEngine',
    ];

    /**
     * The name of the service implementing ViewEngineInterface
     *
     * @var string
     */
    protected $engine;

    /**
     * View engine
     *
     * @var ViewEngineInterface
     */
    protected $viewEngine;

    /**
     * Template file to render
     *
     * @var string
     */
    protected $template;

    /**
     * Template folder
     *
     * @var string
     */
    protected $templateFolder;

    /**
     * Bundle alias (if template should be rendered out of specific bundle)
     *
     * @var string
     */
    protected $bundle;

    /**
     * Variables for the template
     *
     * @var array
     */
    protected $vars = [];

    /**
     * Request
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Response
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Save a variable or an associative array of variables for use inside a template
     *
     * @param mixed $name
     * @param mixed $value
     * @return $this
     */
    public function set($name, $value = null): View
    {
        if (is_array($name)) {
            if (is_array($value)) {
                $data = array_combine($name, $value);
            } else {
                $data = $name;
            }
        } else {
            $data = [$name => $value];
        }
        $this->vars = $data + $this->vars;
        return $this;
    }

    /**
     * Return view engine instance
     *
     * @return ViewEngineInterface
     */
    public function viewEngine(): ViewEngineInterface
    {
        if ($this->engine === null) {
            throw new \Exception('View engine service name not set');
        }
        $viewEngine = $this->container()->get($this->engine);
        if (!($viewEngine instanceof ViewEngineInterface)) {
            throw new \Exception('View engine must implement: ' . ViewEngineInterface::class);
        }
        return $viewEngine;
    }

    /**
     * Render template content
     *
     * @param string|null $template
     * @param string|null $bundle
     * @return string
     * @throws \Exception
     */
    public function render(string $template = null, string $bundle = null): string
    {
        if ($template === null) {
            $template = $this->template;
        }
        if ($bundle === null) {
            $bundle = $this->bundle;
        }

        $template = $this->mediaTypeTemplate($template);

        if ($this->container()->has('template')) {
            $templateInstance = clone $this->container()->get('template');
            $this->set('this', $templateInstance);
            $templateInstance->vars = $this->vars;
            $templateInstance->request = $this->request;
            $templateInstance->response = $this->response;
        }

        return $this->viewEngine()->render($this->path($template), $this->vars, $bundle);
    }

    /**
     * Check if given template exists
     *
     * @param string|null $template
     * @param string|null $bundle
     * @return boolean
     * @throws \Exception
     */
    public function exists(string $template = null, string $bundle = null): bool
    {
        if ($template === null) {
            $template = $this->template;
        }
        if ($bundle === null) {
            $bundle = $this->bundle;
        }
        return $this->viewEngine()->exists($this->path($template), $bundle);
    }

    /**
     * Return template path
     *
     * @param string $template
     * @return string
     */
    protected function path(string $template): string
    {
        if ($template[0] === '/') {
            return ltrim($template, '/');
        }
        if ($this->templateFolder) {
            $template = $this->templateFolder . '/' . $template;
        }
        return $template;
    }

    /**
     * Return template path based on detected media type
     *
     * @param string $template
     * @return string
     * @throws \Exception
     */
    protected function mediaTypeTemplate(string $template): string
    {
        if ($this->request && $this->request->getAttribute('mediaType')) {
            $mediaTemplate = path([$this->request->getAttribute('mediaType')->getSubPart(), $template], '/');
            return ($this->exists($mediaTemplate)) ? $mediaTemplate : $template;
        }
        return $template;
    }

    /**
     * Universal getter/setter
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $method, array $args = [])
    {
        array_unshift($args, $method);
        $self = $this;
        return call_user_func_array(
            function ($property, $value = null) use ($self) {
                if (in_array($property, $self->guarded, true)) {
                    throw new \Exception('Unable to change property: ' . $property);
                }
                if ($value !== null) {
                    $self->{$property} = $value;
                    return $self;
                }
                return $self->{$property};
            },
            $args
        );
    }
}
