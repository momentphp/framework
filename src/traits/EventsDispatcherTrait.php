<?php

namespace momentphp\traits;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;

/**
 * EventsDispatcherTrait
 */
trait EventsDispatcherTrait
{
    /**
     * Dispatcher
     *
     * @var Dispatcher
     */
    protected $eventsDispatcher;

    /**
     * Dispatcher getter/setter
     *
     * @return Dispatcher|object
     */
    public function eventsDispatcher(Dispatcher $dispatcher = null)
    {
        if ($dispatcher !== null) {
            $this->eventsDispatcher = $dispatcher;
            return $this;
        }
        if ($this->eventsDispatcher === null) {
            $this->eventsDispatcher = new Dispatcher(new Container);
        }
        return $this->eventsDispatcher;
    }

    /**
     * Bind implemented events
     *
     * @param object $object
     */
    public function bindImplementedEvents(object $object): void
    {
        if (!method_exists($object, 'implementedEvents')) {
            return;
        }
        foreach ($object->implementedEvents() as $event => $handler) {
            $this->eventsDispatcher()->listen(
                $event,
                function () use ($object, $handler) {
                    $args = func_get_args();
                    array_shift($args);
                    return call_user_func_array([$object, $handler], $args);
                }
            );
        }
    }
}
