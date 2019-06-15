<?php

return [

    'encoding' => 'UTF-8',

    'timezone' => 'UTC',

    'locale' => 'en',

    'error' => [

        'level' => -1,

        'logger' => false,

        'skip' => [
            \Slim\Exception\NotFoundException::class,
            \Slim\Exception\MethodNotAllowedException::class,
        ],

    ],

    'viewEngine' => 'twig',

    'providers' => [
        'smarty' => 'SmartyProvider',
        'twig' => 'TwigProvider',
    ],

    'middlewares' => [
        'app' => [
            'animal' => 'AnimalMiddleware',
        ],
        'route' => [
            'zoo' => 'ZooMiddleware',
        ],
    ],

    'httpVersion' => '1.1',
    'responseChunkSize' => 4096,
    'outputBuffering' => 'prepend',
    'determineRouteBeforeAppMiddleware' => false,
    'displayErrorDetails' => true,

];
