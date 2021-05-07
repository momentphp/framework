<?php

return [
    'Twig' => [
        'templates' => app()->resourcePaths('templates'),
        'compile' => path([app('pathStorage'), 'templates', 'twig', app()->fingerprint()]),
    ],
];
