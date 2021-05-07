<?php

namespace momentphp\tests\app\helpers\subnamespace;

class TestHelper extends \momentphp\Helper
{
    public function test()
    {
        return $this->options('lorem');
    }
}
