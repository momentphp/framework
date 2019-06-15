<?php

namespace momentphp\middlewares;

/**
 * BundleAssetMiddleware
 */
class NegotiationMiddleware extends \momentphp\Middleware
{
    /**
     * Negotiator
     *
     * @var \Negotiation\Negotiator
     */
    protected $negotiator;

    /**
     * Constructor
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param array $options
     */
    public function __construct(\Interop\Container\ContainerInterface $container, $options = [])
    {
        parent::__construct($container, $options);
        $this->negotiator = new \Negotiation\Negotiator;
    }

    /**
     * Invoke middleware
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $acceptHeader = $request->getHeaderLine('accept');
        if (!empty($acceptHeader)) {
            $mediaType = $this->negotiator->getBest($acceptHeader, $this->priorities());
        } else {
            $mediaType = new \Negotiation\Accept($this->priorities()[0]);
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
    protected function priorities()
    {
        return $this->options('mediaType');
    }
}
