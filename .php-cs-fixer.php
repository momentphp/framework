<?php

$header = <<<EOT
This file is part of the `momentphp/framework` package.
EOT;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
;

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
        'ordered_imports' => true,
        'header_comment' => [
            'header' => $header,
        ],
    ])
    ->setFinder($finder)
;
