<?php

namespace momentphp;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;
use Slim\Exception\HttpException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run;

/**
 * ExceptionHandler
 */
class ExceptionHandler
{
    use traits\ContainerTrait;

    protected RequestInterface $request;
    protected ResponseInterface $response;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, RequestInterface $request, ResponseInterface $response)
    {
        $this->container($container);

        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Render exception for CLI
     *
     * @param \Throwable $e
     * @return string
     */
    public function renderForConsole(\Throwable $e): string
    {
        $whoops = $this->whoops();
        $whoops->pushHandler(new PlainTextHandler());
        return $whoops->handleException($e);
    }

    /**
     * Render http response for given exception
     *
     * @param \Throwable $e
     * @return ResponseInterface
     */
    public function renderHttpResponse(\Throwable $e): ResponseInterface
    {
        if ($this->container()->has('debug') && $this->container()->get('debug')) {
            $whoops = $this->whoops();
            $whoops->pushHandler($this->whoopsHandler($e));
            return $this->response->write($whoops->handleException($e))->withStatus(500);
        }

        $errorController = $this->container()->get('registry')->load('ErrorController');

        if ($e instanceof HttpNotFoundException) {
            return $errorController
                ->notFound($e->getRequest())
                ->withStatus(404);
        }

        if ($e instanceof HttpMethodNotAllowedException) {
            return $errorController
                ->notAllowed($e->getRequest(), $e->getAllowedMethods())
                ->withStatus(405)
                ->withHeader('Allow', implode(', ', $e->getAllowedMethods()));
        }

        if ($e instanceof HttpException) {
            $request = $e->getRequest();
        } else {
            $request = $this->request;
        }

        return $errorController->error($request, [$e])->withStatus(500);
    }

    /**
     * Return correct Whoops handler
     *
     * @param \Throwable $e
     * @return HandlerInterface
     */
    protected function whoopsHandler(\Throwable $e)
    {
        $handler = new PrettyPageHandler();
        $handler->setPageTitle('Error');

        $request = ($e instanceof HttpException) ?
            $e->getRequest() :
            $this->request;

        if (!$request->getAttribute('mediaType')) { // NegotiationMiddleware not present
            return $handler;
        }

        switch ($request->getAttribute('mediaType')->getSubPart()) {
            case 'json':
                $handler = new JsonResponseHandler();
                break;
            case 'xml':
                $handler = new XmlResponseHandler();
                break;
        }
        return $handler;
    }

    /**
     * Return prepared Whoops instance
     *
     * @return Run
     */
    protected function whoops(): Run
    {
        $whoops = new Run();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        return $whoops;
    }

    /**
     * Report exception
     *
     * @param \Throwable $e
     */
    public function report(\Throwable $e): void
    {
        if (!$this->container->has('log')) {
            return;
        }

        $options = [];

        if ($this->container->has('config')) {
            $options = $this->container->get('config')->get('app.error', []);
        }

        if (isset($options['skip']) && is_array($options['skip']) && in_array(get_class($e), $options['skip'], true)) {
            return;
        }

        $log = $this->container->get('log');

        if (isset($options['logger']) && $options['logger'] === false) {
            return;
        }

        $log = $log->logger($options['logger']);

        $code = (method_exists($e, 'getSeverity')) ? $e->getSeverity() : E_ERROR;
        $map = $this->defaultErrorLevelMap();
        $level = $map[$code] ?? LogLevel::CRITICAL;

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
    public function defaultErrorLevelMap(): array
    {
        return [
            E_ERROR => LogLevel::CRITICAL,
            E_WARNING => LogLevel::WARNING,
            E_PARSE => LogLevel::ALERT,
            E_NOTICE => LogLevel::NOTICE,
            E_CORE_ERROR => LogLevel::CRITICAL,
            E_CORE_WARNING => LogLevel::WARNING,
            E_COMPILE_ERROR => LogLevel::ALERT,
            E_COMPILE_WARNING => LogLevel::WARNING,
            E_USER_ERROR => LogLevel::ERROR,
            E_USER_WARNING => LogLevel::WARNING,
            E_USER_NOTICE => LogLevel::NOTICE,
            E_STRICT => LogLevel::NOTICE,
            E_RECOVERABLE_ERROR => LogLevel::ERROR,
            E_DEPRECATED => LogLevel::NOTICE,
            E_USER_DEPRECATED => LogLevel::NOTICE,
        ];
    }

    /**
     * Return error code as string
     *
     * @param  $code
     * @return string
     */
    public function codeToString($code): string
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
