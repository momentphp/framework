<?php

namespace momentphp;

/**
 * Database
 */
class Database
{
    use traits\OptionsTrait;

    /**
     * Capsule
     *
     * @var \Illuminate\Database\Capsule\Manager
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
     * @var \Illuminate\Events\Dispatcher
     */
    protected $eventsDispatcher;

    /**
     * Constructor
     *
     * @param array $options
     * @param boolean $debug
     * @param null|\Illuminate\Events\Dispatcher $eventsDispatcher
     */
    public function __construct($options = [], $debug = false, \Illuminate\Events\Dispatcher $eventsDispatcher = null)
    {
        $this->options($options);
        $this->debug = $debug;
        $this->eventsDispatcher = $eventsDispatcher;
    }

    /**
     * Return capsule
     *
     * @return \Illuminate\Database\Capsule\Manager
     */
    public function capsule()
    {
        if ($this->capsule === null) {
            $this->capsule = new \Illuminate\Database\Capsule\Manager;

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
     * @param  null|string $name
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function connection($name = null)
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
    public function queryLog()
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
     * @param  null|string $name
     * @return false|\Illuminate\Database\ConnectionInterface
     */
    public function connectionStatus($name = null)
    {
        try {
            $connection = $this->connection($name);
            return $connection;
        } catch (\Exception $e) {
            return false;
        }
    }
}
