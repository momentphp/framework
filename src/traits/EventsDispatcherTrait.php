<?php

namespace momentphp\traits;

/**
 * EventsDispatcherTrait
 */
trait EventsDispatcherTrait
{
    /**
     * Dispatcher
     *
     * @var \Illuminate\Events\Dispatcher
     */
    protected $eventsDispatcher;

    /**
     * Dispatcher getter/setter
     *
     * @return \Illuminate\Events\Dispatcher|object
     */
    public function eventsDispatcher(\Illuminate\Events\Dispatcher $dispatcher = null)
    {
        if ($dispatcher !== null) {
            $this->eventsDispatcher = $dispatcher;
            return $this;
        }
        if ($this->eventsDispatcher === null) {
            $this->eventsDispatcher = new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container);
        }
        return $this->eventsDispatcher;
    }

    /**
     * Bind implemented events
     *
     * @param object $object
     */
    public function bindImplementedEvents($object)
    {
        if (!method_exists($object, 'implementedEvents')) {
            return;
        }
        foreach ($object->implementedEvents() as $event => $handler) {
            $this->eventsDispatcher()->listen($event, function () use ($object, $handler) {
                $args = func_get_args();
                array_shift($args);
                return call_user_func_array([$object, $handler], $args);
            });
        }
    }
}
