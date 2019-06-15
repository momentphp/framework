<?php

/**
 * Discover & require Composer autoloader (upwards in folders tree)
 */
$pathBase = __DIR__;
do {
    $autoload = implode(DIRECTORY_SEPARATOR, [$pathBase, 'vendor', 'autoload.php']);
    if (file_exists($autoload)) {
        require $autoload;
        break;
    }
    $pathBase = dirname($pathBase);
} while (dirname($pathBase) !== $pathBase);

/**
 * Bundles to load
 */
$bundles = [
    momentphp\test\bundles\first\Bundle::class => ['alias' => 'first'],
    momentphp\test\bundles\second\Bundle::class => ['alias' => 'second'],
    momentphp\test\bundles\third\Bundle::class => ['alias' => 'third'],
    momentphp\test\app\Bundle::class => ['alias' => 'app'],
];

/**
 * Construct app
 */
$app = new momentphp\App($bundles);

/**
 * Send response to the client
 */
$app->run();
