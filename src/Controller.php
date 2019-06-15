<?php

namespace momentphp;

/**
 * Controller
 */
abstract class Controller
{
    use traits\ContainerTrait;
    use traits\OptionsTrait;
    use traits\ClassTrait;

    /**
     * Request
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * Response
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * View
     *
     * @var \momentphp\View
     */
    protected $view;

    /**
     * Constructor
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param array $options
     */
    public function __construct(\Interop\Container\ContainerInterface $container, $options = [])
    {
        $this->container($container);
        $this->options($options);
        if ($this->container()->has('view')) {
            $this->view = clone $this->container()->get('view');
        }
        $this->container()->get('app')->bindImplementedEvents($this);
        $this->container()->get('app')->eventsDispatcher()->fire("controller.{static::classPrefix()}.initialize", [$this]);
    }

    /**
     * Return a list of all events that will fire in the controller during its lifecycle
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            "controller.{static::classPrefix()}.initialize" => 'initialize',
            "controller.{static::classPrefix()}.beforeAction" => 'beforeAction',
            "controller.{static::classPrefix()}.afterAction" => 'afterAction',
        ];
    }

    /**
     * Callback called after controller creation
     */
    public function initialize()
    {
    }

    /**
     * Callback called before the controller action
     *
     * @param string $action
     */
    public function beforeAction($action)
    {
    }

    /**
     * Callback called after the controller action
     *
     * @param string $action
     */
    public function afterAction($action)
    {
    }

    /**
     * Return response object with rendered template
     *
     * @param  null|string $template
     * @param  null|string $bundle
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function render($template = null, $bundle = null)
    {
        $templateFolder = str_replace('.', '/', static::classConfigKey());
        $this->view
        ->request($this->request)
        ->response($this->response)
        ->templateFolder($templateFolder);
        if ($this->view->template() === null) {
            $this->view->template($this->request->getAttribute('action'));
        }
        if ($template) {
            $this->view->template($template);
        }
        if ($bundle) {
            $this->view->bundle($bundle);
        }
        return $this->response->write($this->view->render());
    }

    /**
     * Save a variable or an associative array of variables for use inside a template
     *
     * @param  mixed $name
     * @param  mixed $value
     * @return \momentphp\View
     */
    public function set($name, $value = null)
    {
        return $this->view->set($name, $value);
    }

    /**
     * Not found helper
     *
     * @param null|\Psr\Http\Message\RequestInterface $request
     * @param null|\Psr\Http\Message\ResponseInterface $response
     */
    public function abort($request = null, $response = null)
    {
        if ($request === null) {
            $request = $this->request;
        }
        if ($response === null) {
            $response = $this->response;
        }
        throw new \Slim\Exception\NotFoundException($request, $response);
    }

    /**
     * Invoke action
     *
     * @param  string $action
     * @param  array $args
     * @param  array $options
     * @return string|\Psr\Http\Message\ResponseInterface
     */
    public function __call($action, $args = [])
    {
        if (!method_exists($this, $action)) {
            throw new \Exception('Missing controller action: ' . static::class . ':' . $action);
        }

        $request = $args[0];
        $response = $args[1];
        $params = isset($args[2]) ? $args[2] : [];

        $attributes = $request->getAttributes();
        $attributes['controller'] = static::classBasename();
        $attributes['action'] = $action;
        $request = $request->withAttributes($attributes);

        $this->request = $request;
        $this->response = $response;

        $this->container()->get('app')->eventsDispatcher()->fire("controller.{static::classPrefix()}.beforeAction", [$this, $action]);
        $actionResponse = call_user_func_array([$this, $action], $params);
        $this->container()->get('app')->eventsDispatcher()->fire("controller.{static::classPrefix()}.afterAction", [$this, $action]);

        if ($actionResponse === null) {
            if ($this->view === null) {
                throw new \Exception('View instance not set');
            }
            $actionResponse = $this->render();
        }

        return $actionResponse;
    }

    /**
     * Return model
     *
     * @param  string $name
     * @return \momentphp\Model|\momentphp\Registry
     */
    public function __get($name)
    {
        return $this->container()->has('registry') ? $this->container()->get('registry')->models->{$name} : $this->{$name};
    }
}
