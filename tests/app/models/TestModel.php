<?php

namespace momentphp\tests\app\models;

class TestModel extends \momentphp\Model
{
    protected $str = ['hello'];

    public function initialize(): void
    {
        $this->str[] = 'world';
    }

    public function greet(): string
    {
        return implode(' ', $this->str);
    }
}
