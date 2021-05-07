<?php

namespace momentphp\tests\app\controllers;

class TestController extends \momentphp\Controller
{
    public $callbacksCount = 0;

    public function initialize(): void
    {
        $this->callbacksCount++;
    }

    public function beforeAction(string $action): void
    {
        if ($action === 'callbacks') {
            $this->callbacksCount++;
        }
    }

    public function afterAction(string $action): void
    {
        if ($action === 'callbacks') {
            $this->callbacksCount++;
        }
    }

    protected function hello($name = 'stranger'): string
    {
        return 'hello ' . $name;
    }

    protected function callbacks(): string
    {
        $this->callbacksCount++;
        return '';
    }

    protected function attributes(): string
    {
        return sprintf('%s:%s', $this->request->getAttribute('controller'), $this->request->getAttribute('action'));
    }

    protected function template(): void
    {
    }

    protected function template2(): void
    {
        $this->view->template('template');
    }

    protected function template3(): void
    {
        $this->view->template('/template3');
    }

    protected function helper($viewEngine = 'twig'): void
    {
        $this->view->engine($viewEngine);
    }

    protected function cell(): void
    {
    }
}
