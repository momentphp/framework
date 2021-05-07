<?php

namespace momentphp;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Events\Dispatcher;

/**
 * Database
 */
class Database
{
    use traits\OptionsTrait;

    /**
     * Capsule
     *
     * @var Manager
     */
    protected $capsule;

    /**
     * Debug flag
     *
     * @var bool
     */
    protected $debug;

    /**
     * Dispatcher
     *
     * @var Dispatcher
     */
    protected $eventsDispatcher;

    /**
     * Constructor
     *
     * @param array $options
     * @param boolean $debug
     * @param null|Dispatcher $eventsDispatcher
     */
    public function __construct(array $options = [], bool $debug = false, ?Dispatcher $eventsDispatcher = null)
    {
        $this->options($options);
        $this->debug = $debug;
        $this->eventsDispatcher = $eventsDispatcher;
    }

    /**
     * Return capsule
     *
     * @return Manager
     */
    public function capsule(): Manager
    {
        if ($this->capsule === null) {
            $this->capsule = new Manager;

            if ($this->eventsDispatcher) {
                $this->capsule->setEventDispatcher($this->eventsDispatcher);
            }

            if ($this->options('fetch')) {
                $this->capsule->setFetchMode($this->options('fetch'));
            }

            if ($this->options('connections')) {
                foreach ($this->options('connections') as $name => $settings) {
                    $this->capsule->addConnection($settings, $name);
                }
            }
        }
        return $this->capsule;
    }

    /**
     * Return connection
     *
     * @param string|null $name
     * @return ConnectionInterface
     */
    public function connection(?string $name = null): ConnectionInterface
    {
        if ($name === null) {
            if (!$this->options('default')) {
                throw new \Exception('Default connection name must be set');
            }
            $name = $this->options('default');
        }
        $connection = $this->capsule()->getConnection($name);
        if ($this->debug && !$connection->logging()) {
            $connection->enableQueryLog();
        }
        return $connection;
    }

    /**
     * Return query log from all connections
     *
     * @return array
     */
    public function queryLog(): array
    {
        $queryLog = [];
        if ($this->options('connections')) {
            foreach (array_keys($this->options('connections')) as $name) {
                try {
                    $log = $this->connection($name)->getQueryLog();
                    if (!empty($log)) {
                        $queryLog[$name] = $log;
                    }
                } catch (\Exception $e) {
                }
            }
        }
        return $queryLog;
    }

    /**
     * Return connection status
     *
     * @param string|null $name
     * @return false|ConnectionInterface
     */
    public function connectionStatus(string $name = null)
    {
        try {
            return $this->connection($name);
        } catch (\Exception $e) {
            return false;
        }
    }
}
