<?php

namespace momentphp\tests\app\controllers;

class TestController extends \momentphp\Controller
{
    public $callbacksCount = 0;

    public function initialize()
    {
        $this->callbacksCount++;
    }

    public function beforeAction($action)
    {
        if ($action === 'callbacks') {
            $this->callbacksCount++;
        }
    }

    public function afterAction($action)
    {
        if ($action === 'callbacks') {
            $this->callbacksCount++;
        }
    }

    protected function hello($name = 'stranger')
    {
        return 'hello ' . $name;
    }

    protected function callbacks()
    {
        $this->callbacksCount++;
        return '';
    }

    protected function attributes()
    {
        return sprintf('%s:%s', $this->request->getAttribute('controller'), $this->request->getAttribute('action'));
    }

    protected function template()
    {
    }

    protected function template2()
    {
        $this->view->template('template');
    }

    protected function template3()
    {
        $this->view->template('/template3');
    }

    protected function helper($viewEngine = 'smarty')
    {
        $this->view->engine($viewEngine);
    }

    protected function cell()
    {
    }
}
