<?php

namespace momentphp;

/**
 * ExceptionHandler
 */
class ExceptionHandler
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
     * Render exception for CLI
     *
     * @param  \Throwable $e
     * @return string
     */
    public function renderForConsole($e)
    {
        $whoops = $this->whoops();
        $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler);
        return $whoops->handleException($e);
    }

    /**
     * Render http response for given exception
     *
     * @param  \Throwable $e
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function renderHttpResponse($e)
    {
        if ($this->container()->has('debug')) {
            if ($this->container()->get('debug')) {
                $whoops = $this->whoops();
                $whoops->pushHandler($this->whoopsHandler($e));
                return $this->container->get('response')->write($whoops->handleException($e))->withStatus(500);
            }
        }

        $errorController = $this->container()->get('registry')->load('ErrorController');

        if ($e instanceof \Slim\Exception\NotFoundException) {
            return $errorController
            ->notFound($e->getRequest(), $e->getResponse())
            ->withStatus(404);
        }

        if ($e instanceof \Slim\Exception\MethodNotAllowedException) {
            return $errorController
            ->notAllowed($e->getRequest(), $e->getResponse(), $e->getAllowedMethods())
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $e->getAllowedMethods()));
        }

        if ($e instanceof \Slim\Exception\SlimException) {
            $request = $e->getRequest();
            $response = $e->getResponse();
        } else {
            $request = $this->container()->get('request');
            $response = $this->container()->get('response');
        }

        return $errorController->error($request, $response, [$e])->withStatus(500);
    }

    /**
     * Return correct Whoops handler
     *
     * @param  \Throwable $e
     * @return \Whoops\Handler\HandlerInterface
     */
    protected function whoopsHandler($e)
    {
        $handler = new \Whoops\Handler\PrettyPageHandler;
        $handler->setPageTitle('Error');

        $request = ($e instanceof \Slim\Exception\SlimException) ?
            $e->getRequest() :
            $this->container()->get('request');

        if (!$request->getAttribute('mediaType')) { // NegotiationMiddleware not present
            return $handler;
        }

        switch ($request->getAttribute('mediaType')->getSubPart()) {
            case 'json':
                $handler = new \Whoops\Handler\JsonResponseHandler;
                break;
            case 'xml':
                $handler = new \Whoops\Handler\XmlResponseHandler;
                break;
        }
        return $handler;
    }

    /**
     * Return prepared Whoops instance
     *
     * @return \Whoops\Run
     */
    protected function whoops()
    {
        $whoops = new \Whoops\Run;
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        return $whoops;
    }

    /**
     * Report exception
     *
     * @param \Throwable $e
     */
    public function report($e)
    {
        if (!$this->container->has('log')) {
            return;
        }

        $options = [];

        if ($this->container->has('config')) {
            $options = $this->container->get('config')->get('app.error', []);
        }

        if (isset($options['skip']) && is_array($options['skip'])) {
            if (in_array(get_class($e), $options['skip'])) {
                return;
            }
        }

        $log = $this->container->get('log');

        if (isset($options['logger']) && $options['logger'] === false) {
            return;
        } else {
            $log = $log->logger($options['logger']);
        }

        $code = (method_exists($e, 'getSeverity')) ? $e->getSeverity() : E_ERROR;
        $map = $this->defaultErrorLevelMap();
        $level = isset($map[$code]) ? $map[$code] : \Psr\Log\LogLevel::CRITICAL;

        $message = $e->getMessage();
        if (empty($message)) {
            $message = sprintf('Uncaught exception: %s', get_class($e));
        }

        $message = $this->codeToString($code) . ': ' . $message;

        $context = [
            'exception' => $e,
        ];

        $log->log($level, $message, $context);
    }

    /**
     * Error level map
     *
     * @return array
     */
    public function defaultErrorLevelMap()
    {
        return [
            E_ERROR             => \Psr\Log\LogLevel::CRITICAL,
            E_WARNING           => \Psr\Log\LogLevel::WARNING,
            E_PARSE             => \Psr\Log\LogLevel::ALERT,
            E_NOTICE            => \Psr\Log\LogLevel::NOTICE,
            E_CORE_ERROR        => \Psr\Log\LogLevel::CRITICAL,
            E_CORE_WARNING      => \Psr\Log\LogLevel::WARNING,
            E_COMPILE_ERROR     => \Psr\Log\LogLevel::ALERT,
            E_COMPILE_WARNING   => \Psr\Log\LogLevel::WARNING,
            E_USER_ERROR        => \Psr\Log\LogLevel::ERROR,
            E_USER_WARNING      => \Psr\Log\LogLevel::WARNING,
            E_USER_NOTICE       => \Psr\Log\LogLevel::NOTICE,
            E_STRICT            => \Psr\Log\LogLevel::NOTICE,
            E_RECOVERABLE_ERROR => \Psr\Log\LogLevel::ERROR,
            E_DEPRECATED        => \Psr\Log\LogLevel::NOTICE,
            E_USER_DEPRECATED   => \Psr\Log\LogLevel::NOTICE,
        ];
    }

    /**
     * Return error code as string
     *
     * @param  const $code
     * @return string
     */
    public function codeToString($code)
    {
        switch ($code) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
        }
        return 'Unknown PHP error';
    }
}
