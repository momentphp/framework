<?php

namespace momentphp\tests\cases\traits;

use PHPUnit_Framework_TestCase;

class OptionsTraitConsumer
{
    use \momentphp\traits\OptionsTrait;

    protected $defaults = [
        'lorem' => 'ipsum',
        'some' => ['deep' => 'option'],
    ];
}

class OptionsTraitTest extends PHPUnit_Framework_TestCase
{
    public $consumer;

    public function setUp()
    {
        $this->consumer = new OptionsTraitConsumer;
    }

    public function tearDown()
    {
        unset($this->consumer);
    }

    public function testGet()
    {
        $this->assertEquals([
            'lorem' => 'ipsum',
            'some' => ['deep' => 'option'],
        ], $this->consumer->options());

        $this->assertEquals('ipsum', $this->consumer->options('lorem'));
        $this->assertEquals('option', $this->consumer->options('some.deep'));
        $this->assertEquals(null, $this->consumer->options('notfound'));
    }

    public function testSet()
    {
        $result = $this->consumer->options('new', 'option');
        $this->assertEquals('option', $this->consumer->options('new'));
        $this->assertInstanceOf(OptionsTraitConsumer::class, $result);

        $this->consumer->options('some.deep', 'option2');
        $this->assertEquals(['deep' => 'option2'], $this->consumer->options('some'));
    }

    public function testSetArray()
    {
        $this->consumer->options(['lorem' => 'ipsum2', 'some.deep' => 'option2']);
        $this->assertEquals([
            'lorem' => 'ipsum2',
            'some' => ['deep' => 'option2'],
        ], $this->consumer->options());
    }

    public function testSetReplace()
    {
        $this->consumer->options(null, ['new' => 'array']);
        $this->assertEquals(['new' => 'array'], $this->consumer->options());
    }
}
