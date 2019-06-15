<?php

namespace momentphp;

/**
 * Model
 */
abstract class Model
{
    use traits\ContainerTrait;
    use traits\OptionsTrait;
    use traits\ClassTrait;

    /**
     * Connection name to use
     *
     * @var string
     */
    protected $connection;

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
        $this->container()->get('app')->bindImplementedEvents($this);
        $this->container()->get('app')->eventsDispatcher()->fire("model.{static::classPrefix()}.initialize", [$this]);
    }

    /**
     * Return connection
     *
     * @param  null|string $connectionName
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function db($connectionName = null)
    {
        if ($connectionName === null) {
            $connectionName = $this->connection;
        }
        if ($connectionName === null) {
            $connection = $this->container()->get('database')->connection();
        } else {
            $connection = $this->container()->get('database')->connection($connectionName);
        }
        return $connection;
    }

    /**
     * Return a list of all events that will fire in the model during its lifecycle
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            "model.{static::classPrefix()}.initialize" => 'initialize'
        ];
    }

    /**
     * Model callback invoked just after model construction
     */
    public function initialize()
    {
    }

    /**
     * Return model key (for caching purposes)
     *
     * @return string
     */
    public function objectKey()
    {
        return get_class($this) . serialize($this->options());
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
