<?php

namespace momentphp\tests\cases;

use momentphp\App;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SkipTest extends Test
{
    public function testSkip()
    {
        $app = new App(
            [
                \momentphp\tests\bundles\first\Bundle::class => ['alias' => 'first', 'skipResource' => ['routes']],
                \momentphp\tests\bundles\second\Bundle::class => ['alias' => 'second', 'skipResource' => ['routes']],
                \momentphp\tests\bundles\third\Bundle::class => [
                    'alias' => 'third',
                    'skipClass' => 'classes',
                ],
                \momentphp\tests\app\Bundle::class => ['alias' => 'app', 'skipResource' => ['routes']],
            ]
        );
        $app->booted = true;
        $app->registerRoutes();

        $cl = $app->bundleClass('classes\Animal');
        $animal = new $cl;
        self::assertEquals('zebra from second bundle', $animal->makeNoise());

        $res = $app->visit('/animal');
        self::assertEquals('monkey from third bundle', (string)$res->getBody());
    }
}
