<?php

namespace momentphp\tests\app\helpers;

class TestHelper extends \momentphp\Helper
{
    public function test()
    {
        return $this->options('lorem');
    }
}
