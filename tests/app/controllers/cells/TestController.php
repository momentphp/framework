<?php

namespace momentphp\tests\app\controllers\cells;

class TestController extends \momentphp\Controller
{
    protected function display($limit = 5)
    {
        $limit = $this->options('limit') + $limit;
        $this->set('limit', (string) $limit);
    }
}
