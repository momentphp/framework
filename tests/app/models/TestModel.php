<?php

namespace momentphp\tests\app\models;

class TestModel extends \momentphp\Model
{
    protected $str = ['hello'];

    public function initialize()
    {
        $this->str[] = 'world';
    }

    public function greet()
    {
        return implode(' ', $this->str);
    }
}
