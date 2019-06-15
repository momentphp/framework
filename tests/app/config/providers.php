<?php

return [
    'Smarty' => [
        'templates' => app()->resourcePaths('templates'),
        'compile' => path([app('pathStorage'), 'templates', 'smarty', app()->fingerprint()]),
    ],
    'Twig' => [
        'templates' => app()->resourcePaths('templates'),
        'compile' => path([app('pathStorage'), 'templates', 'twig', app()->fingerprint()]),
    ],
];
