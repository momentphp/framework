<?php

namespace momentphp;

/**
 * Router
 */
class Router extends \Slim\Router
{
    /**
     * @return \FastRoute\Dispatcher
     */
    protected function createDispatcher()
    {
        return $this->dispatcher ?: \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            foreach ($this->getRoutes() as $route) {
                try {
                    $r->addRoute($route->getMethods(), $route->getPattern(), $route->getIdentifier());
                } catch (\FastRoute\BadRouteException $e) {
                }
            }
        }, [
          'routeParser' => $this->routeParser
        ]);
    }
}
