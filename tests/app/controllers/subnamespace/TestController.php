<?php

namespace momentphp\tests\app\controllers\subnamespace;

class TestController extends \momentphp\Controller
{
    protected function hello($name = 'stranger'): string
    {
        return 'hello ' . $name;
    }

    protected function template(): void
    {
    }
}
