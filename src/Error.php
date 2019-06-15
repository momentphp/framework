<?php

namespace momentphp;

/**
 * Error
 */
class Error
{
    use traits\ContainerTrait;

    /**
     * Constructor
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(\Interop\Container\ContainerInterface $container)
    {
        $this->container($container);
    }

    /**
     * Register framework error handlers
     */
    public function register()
    {
        $container = $this->container();

        if ($container->has('config')) {
            error_reporting($container->get('config')->get('app.error.level', -1));
        }

        if ($container->has('debug') && ($container->get('debug') == false)) {
            ini_set('display_startup_errors', 0);
            ini_set('display_errors', 0);
        }

        if ($container->has('exceptionHandler')) {
            unset($container['notFoundHandler']);
            unset($container['notAllowedHandler']);
            unset($container['errorHandler']);
            unset($container['phpErrorHandler']);
            set_error_handler([$this, 'handleError']);
            set_exception_handler([$this, 'handleException']);
            register_shutdown_function([$this, 'handleShutdown']);
        }
    }

    /**
     * Convert a PHP error to an ErrorException
     *
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $context
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an uncaught exception from the app
     *
     * @param  \Throwable $e
     * @return void|\Psr\Http\Message\ResponseInterface
     */
    public function handleException($e)
    {
        $handler = $this->container->get('exceptionHandler');
        $handler->report($e);

        if ($this->container()->has('console') && $this->container()->get('console')) {
            echo $handler->renderForConsole($e);
            exit(1);
        } else {
            $response = $handler->renderHttpResponse($e);
            return $this->container()->get('app')->respond($response);
        }
    }

    /**
     * Shutdown function
     */
    public function handleShutdown()
    {
        $error = error_get_last();
        if (!is_array($error)) {
            return;
        }
        if (!$this->isFatal($error['type'])) {
            return;
        }
        $e = new exceptions\FatalErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line'],
            0
        );
        $this->handleException($e);
    }

    /**
     * Determine if the error type is fatal
     *
     * @param  int $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_USER_ERROR]);
    }
}
