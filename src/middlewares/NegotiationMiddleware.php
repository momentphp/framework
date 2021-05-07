<?php

namespace momentphp\middlewares;

use momentphp\Middleware;
use Negotiation\Accept;
use Negotiation\Negotiator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * BundleAssetMiddleware
 */
class NegotiationMiddleware extends Middleware
{
    /**
     * Negotiator
     *
     * @var Negotiator
     */
    protected $negotiator;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param array $options
     */
    public function __construct(ContainerInterface $container, array $options = [])
    {
        parent::__construct($container, $options);
        $this->negotiator = new Negotiator;
    }

    /**
     * Invoke middleware
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     * @throws \Negotiation\Exception\Exception
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $acceptHeader = $request->getHeaderLine('accept');
        if (!empty($acceptHeader)) {
            $mediaType = $this->negotiator->getBest($acceptHeader, $this->priorities());
        } else {
            $mediaType = new Accept($this->priorities()[0]);
        }
        if (!$mediaType) {
            return $next($request, $response);
        }
        $request = $request->withAttribute('mediaType', $mediaType);
        $response = $response->withHeader('Content-Type', $mediaType->getValue());
        return $next($request, $response);
    }

    /**
     * Return priorities
     *
     * @return array
     */
    protected function priorities(): array
    {
        return $this->options('mediaType');
    }
}
