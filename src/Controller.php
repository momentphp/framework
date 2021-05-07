<?php

namespace momentphp;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;

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
     * View
     *
     * @var View
     */
    protected $view;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param array $options
     */
    public function __construct(ContainerInterface $container, array $options = [])
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
    public function implementedEvents(): array
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
    public function initialize(): void
    {
    }

    /**
     * Callback called before the controller action
     *
     * @param string $action
     */
    public function beforeAction(string $action): void
    {
    }

    /**
     * Callback called after the controller action
     *
     * @param string $action
     */
    public function afterAction(string $action): void
    {
    }

    /**
     * Return response object with rendered template
     *
     * @param string|null $template
     * @param string|null $bundle
     * @return ResponseInterface
     */
    public function render(?string $template = null, ?string $bundle = null): ResponseInterface
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
        $this->response->getBody()->write($this->view->render());

        return $this->response;
    }

    /**
     * Save a variable or an associative array of variables for use inside a template
     *
     * @param mixed $name
     * @param mixed $value
     * @return View
     */
    public function set($name, $value = null): View
    {
        return $this->view->set($name, $value);
    }

    /**
     * Not found helper
     *
     * @param RequestInterface|null $request
     * @param ResponseInterface|null $response
     * @throws HttpNotFoundException
     */
    public function abort(?RequestInterface $request = null, ?ResponseInterface $response = null): void
    {
        if ($request === null) {
            $request = $this->request;
        }
        if ($response === null) {
            $response = $this->response;
        }
        throw new HttpNotFoundException($request);
    }

    /**
     * Invoke action
     *
     * @param string $action
     * @param array $args
     * @return string|ResponseInterface
     * @throws \Exception
     */
    public function __call(string $action, array $args = [])
    {
        if (!method_exists($this, $action)) {
            throw new \Exception('Missing controller action: ' . static::class . ':' . $action);
        }

        $request = $args[0];
        $response = $args[1];
        $params = $args[2] ?? [];

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
     * @param string $name
     * @return Model|Registry
     */
    public function __get($name)
    {
        return $this->container()->has('registry') ? $this->container()->get('registry')->models->{$name} : $this->{$name};
    }
}
