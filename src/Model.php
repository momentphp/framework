<?php

namespace momentphp;

use Illuminate\Database\ConnectionInterface;
use Psr\Container\ContainerInterface;

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
     * @param ContainerInterface $container
     * @param array $options
     */
    public function __construct(ContainerInterface $container, array $options = [])
    {
        $this->container($container);
        $this->options($options);
        $this->container()->get('app')->bindImplementedEvents($this);
        $this->container()->get('app')->eventsDispatcher()->dispatch("model.{static::classPrefix()}.initialize", [$this]);
    }

    /**
     * Return connection
     *
     * @param string|null $connectionName
     * @return ConnectionInterface
     */
    public function db(string $connectionName = null): ConnectionInterface
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
    public function implementedEvents(): array
    {
        return [
            "model.{static::classPrefix()}.initialize" => 'initialize'
        ];
    }

    /**
     * Model callback invoked just after model construction
     */
    public function initialize(): void
    {
    }

    /**
     * Return model key (for caching purposes)
     *
     * @return string
     */
    public function objectKey(): string
    {
        return get_class($this) . serialize($this->options());
    }

    /**
     * Return model
     *
     * @param string $name
     * @return Model|Registry
     */
    public function __get(string $name)
    {
        return $this->container()->has('registry') ? $this->container()->get('registry')->models->{$name} : $this->{$name};
    }
}
