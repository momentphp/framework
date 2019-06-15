<?php

namespace momentphp\tests\cases;

class AppTest extends Test
{
    public $app;

    public function setUp()
    {
        $this->app = new \momentphp\App([
            \momentphp\tests\bundles\first\Bundle::class => ['alias' => 'first'],
            \momentphp\tests\bundles\second\Bundle::class => ['alias' => 'second'],
            \momentphp\tests\bundles\third\Bundle::class => ['alias' => 'third'],
            \momentphp\tests\app\Bundle::class => ['alias' => 'app'],
        ], [
            'settings' => ['displayErrorDetails' => true]
        ]);
    }

    public function tearDown()
    {
        unset($this->app);
    }

    public function testRouteOverwriting()
    {
        $app = $this->app;
        $app->booted = true;
        $app->get('/test', function () {
            return 'first';
        });
        $app->get('/test', function () {
            return 'second';
        })->setName('second');
        $res = $app->visit('/test');
        $this->assertEquals('first', (string) $res->getBody());
    }

    public function testResourcePaths()
    {
        $app = $this->app;
        $app->booted = true;

        $this->assertEquals([
            $app->bundles('first')->classPath('config'),
            $app->bundles('second')->classPath('config'),
            $app->bundles('third')->classPath('config'),
            $app->bundles('app')->classPath('config'),
        ], $app->resourcePaths('config'));

        $this->assertEquals([
            $app->bundles('app')->alias() => $app->bundles('app')->classPath('templates'),
            $app->bundles('third')->alias() => $app->bundles('third')->classPath('templates'),
            $app->bundles('second')->alias() => $app->bundles('second')->classPath('templates'),
            $app->bundles('first')->alias() => $app->bundles('first')->classPath('templates'),
        ], $app->resourcePaths('templates'));

        $this->assertEquals([
            $app->bundles('app')->classPath('routes.php'),
            $app->bundles('third')->classPath('routes.php'),
            $app->bundles('second')->classPath('routes.php'),
            $app->bundles('first')->classPath('routes.php'),
        ], $app->resourcePaths('routes'));
    }

    public function testFingerprint()
    {
        $app = $this->app;
        $app->booted = true;
        $aliases = 'first_second_third_app';
        $this->assertEquals($aliases, $app->fingerprint());
        $aliases = 'first.second.third.app';
        $this->assertEquals($aliases, $app->fingerprint('.'));
    }

    public function testAutoloader()
    {
        $app = $this->app;
        $app->booted = true;
        $class = $app->bundleClass('classes\Animal');
        $animal = new $class;
        $this->assertEquals('monkey from third bundle', $animal->makeNoise());
    }

    public function testCallableResolver()
    {
        $app = $this->app;
        $app->booted = true;
        $app->get('/hello/{name}', 'TestController:hello');
        $app->get('/subnamespace/hello/{name}', 'subnamespace\TestController:hello');
        $res = $app->visit('/hello/john');
        $this->assertEquals('hello john', (string) $res->getBody());
        $res = $app->visit('/subnamespace/hello/john');
        $this->assertEquals('hello john', (string) $res->getBody());
    }

    public function testControllerCallbacks()
    {
        $app = $this->app;
        $app->booted = true;
        $controllerClass = $app->bundleClass('controllers\TestController');
        $controller = new $controllerClass(new \Slim\Container([
            'app' => function () use ($app) {
                return $app;
            }
        ]));
        $controller->callbacks($app->request, $app->response, []);
        $this->assertEquals(4, $controller->callbacksCount);
    }

    public function testRequestAttributes()
    {
        $app = $this->app;
        $app->booted = true;
        $app->get('/attributes', 'TestController:attributes');
        $res = $app->visit('/attributes');
        $this->assertEquals('TestController:attributes', (string) $res->getBody());
    }

    public function testMiddlewares()
    {
        $app = $this->app;
        $app->boot();
        $app->get('/route-middleware', function () {
            return 'route middleware';
        })->add('zoo');
        $res = $app->visit('/');
        $this->assertEquals(['lion'], $res->getHeader('X-Animal'));
        $res = $app->visit('/route-middleware');
        $this->assertEquals(['lion'], $res->getHeader('X-Zoo'));
    }

    public function testViewEngines()
    {
        $app = $this->app;
        $app->registerProviders();

        $this->assertEquals('monkey james from third bundle', trim($app->smarty->render('animal', ['name' => 'james'])));
        $this->assertEquals('monkey james from third bundle', trim($app->twig->render('animal', ['name' => 'james'])));

        $this->assertEquals('zebra james from second bundle', trim($app->smarty->render('animal', ['name' => 'james'], 'second')));
        $this->assertEquals('zebra james from second bundle', trim($app->twig->render('animal', ['name' => 'james'], 'second')));

        $this->assertEquals(true, trim($app->smarty->exists('animal', 'third')));
        $this->assertEquals(true, trim($app->twig->exists('animal', 'third')));

        $this->assertEquals(false, trim($app->smarty->exists('nonexistenttemplate', 'third')));
        $this->assertEquals(false, trim($app->twig->exists('nonexistenttemplate', 'third')));
    }

    public function testView()
    {
        $app = $this->app;
        $app->registerProviders();
        $view = clone $app->view;
        $this->assertEquals('monkey james from third bundle', trim($view->template('animal')->set('name', 'james')->render()));
        $view = clone $app->view;
        $this->assertEquals('monkey james from third bundle', trim($view->set('name', 'james')->render('animal')));
        $view = clone $app->view;
        $this->assertEquals('zebra', trim($view->render('/zoo/animals/zebra')));
        $view->templateFolder('zoo/animals');
        $this->assertEquals('zebra', trim($view->template('zebra')->render()));
        $this->assertEquals('lion', trim($view->template('lion')->render()));
        $this->assertEquals('monkey james from third bundle', trim($view->template('/animal')->set('name', 'james')->render()));
    }

    public function testControllerTemplates()
    {
        $app = $this->app;
        $app->boot();
        $app->get('/template', 'TestController:template');
        $app->get('/template2', 'TestController:template2');
        $app->get('/template3', 'TestController:template3');
        $app->get('/subnamespace/template', 'subnamespace\TestController:template');
        $res = $app->visit('/template');
        $this->assertEquals('template', trim((string) $res->getBody()));
        $res = $app->visit('/template2');
        $this->assertEquals('template', trim((string) $res->getBody()));
        $res = $app->visit('/template3');
        $this->assertEquals('template3', trim((string) $res->getBody()));
        $res = $app->visit('/subnamespace/template');
        $this->assertEquals('subnamespace template', trim((string) $res->getBody()));
    }

    public function testHelpers()
    {
        $app = $this->app;
        $app->boot();
        $app->get('/helper/{viewService}', 'TestController:helper');
        $res = $app->visit('/helper/smarty');
        $this->assertEquals('ipsumipsums', trim((string)$res->getBody()));
        $res = $app->visit('/helper/twig');
        $this->assertEquals('ipsumipsums', trim((string)$res->getBody()));
    }

    public function testCells()
    {
        $app = $this->app;
        $app->boot();
        $app->get('/cell', 'TestController:cell');
        $res = $app->visit('/cell');
        $this->assertEquals('110', trim((string)$res->getBody()));
    }

    public function testModels()
    {
        $app = $this->app;
        $this->assertEquals('hello world', $app->registry->models->Test->greet());
        $model1 = $app->registry->models->Test;
        $model2 = $app->registry->models->Test;
        $same = (spl_object_hash($model1) === spl_object_hash($model1));
        $this->assertEquals(true, $same);
        $this->assertEquals('subnamespace hello world', $app->registry->models->deep->subnamespace->Test->greet());
    }

    public function testCache()
    {
        $app = $this->app;
        $this->assertEquals(null, $app->cache->get('lorem'));
        $app->cache->put('lorem', 'ipsum', 1);
        $this->assertEquals('ipsum', $app->cache->get('lorem'));
        $app->cache->forget('lorem');
        if (!class_exists('Memcached')) {
            return;
        }
        $memcached = $app->cache->store('memcached');
        $this->assertEquals(null, $memcached->get('lorem'));
        $memcached->put('lorem', 'ipsum', 1);
        $this->assertEquals('ipsum', $memcached->get('lorem'));
        $memcached->forget('lorem');
    }
}
