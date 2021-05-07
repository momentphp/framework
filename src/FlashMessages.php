<?php

namespace momentphp;

use ArrayAccess;

/**
 * FlashMessages
 */
class FlashMessages
{
    use traits\OptionsTrait;

    /**
     * View
     *
     * @var View
     */
    protected $view;

    /**
     * Messages storage
     *
     * @var null|array|ArrayAccess
     */
    protected $storage;

    /**
     * Messages storage key
     *
     * @var string
     */
    protected $storageKey = 'flash';

    /**
     * Default configuration
     *
     * @var array
     */
    protected $defaults = [
        'key' => 'flash',
        'template' => 'default',
        'bundle' => null,
        'params' => [],
        'clear' => false,
    ];

    /**
     * Constructor
     *
     * @param View $view
     * @param null|array|ArrayAccess &$storage
     */
    public function __construct(View $view, &$storage = null)
    {
        $this->view = $view;
        if (is_array($storage) || $storage instanceof ArrayAccess) {
            $this->storage = &$storage;
        } elseif (is_null($storage)) {
            if (!isset($_SESSION)) {
                throw new \RuntimeException('Flash messages failed - session not found');
            }
            $this->storage = &$_SESSION;
        } else {
            throw new \InvalidArgumentException('Flash messages storage must be an array or implement \ArrayAccess');
        }
        if (!isset($this->storage[$this->storageKey])) {
            $this->storage[$this->storageKey] = [];
        }
    }

    /**
     * Set a flash message
     *
     * @param string $message
     * @param array $options
     */
    public function set(string $message, array $options = [])
    {
        $options += $this->options();
        $messages = [];
        if (($options['clear'] === false) && (isset($this->storage[$this->storageKey][$options['key']]))) {
            $messages = $this->storage[$this->storageKey][$options['key']];
        }
        $messages[] = [
            'message' => $message,
            'key' => $options['key'],
            'template' => $options['template'],
            'bundle' => $options['bundle'],
            'params' => $options['params'],
        ];
        $this->storage[$this->storageKey][$options['key']] = $messages;
    }

    /**
     * Render flash messages under given key
     *
     * @param string $key
     * @return string|null
     */
    public function render(string $key = 'flash'): ?string
    {
        if (!isset($this->storage[$this->storageKey][$key])) {
            return null;
        }
        $flash = $this->storage[$this->storageKey][$key];
        unset($this->storage[$this->storageKey][$key]);
        $out = '';
        foreach ($flash as $message) {
            $view = clone $this->view;
            $out .= $view->templateFolder('partials/flash')->set($message)->render($message['template'], $message['bundle']);
        }
        return $out;
    }

    /**
     * Proxy calls to `set` method
     *
     * @param string $method
     * @param array $args
     */
    public function __call(string $method, array $args = [])
    {
        $template = $method;
        if (count($args) < 1) {
            throw new \Exception('Flash message missing');
        }
        $options = ['template' => $template];
        if (!empty($args[1])) {
            $options += (array)$args[1];
        }
        $this->set($args[0], $options);
    }
}
