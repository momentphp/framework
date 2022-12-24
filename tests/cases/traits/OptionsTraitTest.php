<?php

namespace momentphp\tests\cases\traits;

use momentphp\traits\OptionsTrait;
use PHPUnit\Framework\TestCase;

class OptionsTraitConsumer
{
    use OptionsTrait;

    protected $defaults = [
        'lorem' => 'ipsum',
        'some' => ['deep' => 'option'],
    ];
}

class OptionsTraitTest extends TestCase
{
    public $consumer;

    public function setUp(): void
    {
        $this->consumer = new OptionsTraitConsumer;
    }

    public function tearDown(): void
    {
        unset($this->consumer);
    }

    public function testGet()
    {
        self::assertEquals(
            [
                'lorem' => 'ipsum',
                'some' => ['deep' => 'option'],
            ],
            $this->consumer->options()
        );

        self::assertEquals('ipsum', $this->consumer->options('lorem'));
        self::assertEquals('option', $this->consumer->options('some.deep'));
        self::assertEquals(null, $this->consumer->options('notfound'));
    }

    public function testSet()
    {
        $result = $this->consumer->options('new', 'option');
        self::assertEquals('option', $this->consumer->options('new'));
        self::assertInstanceOf(OptionsTraitConsumer::class, $result);

        $this->consumer->options('some.deep', 'option2');
        self::assertEquals(['deep' => 'option2'], $this->consumer->options('some'));
    }

    public function testSetArray()
    {
        $this->consumer->options(['lorem' => 'ipsum2', 'some.deep' => 'option2']);
        self::assertEquals(
            [
                'lorem' => 'ipsum2',
                'some' => ['deep' => 'option2'],
            ],
            $this->consumer->options()
        );
    }

    public function testSetReplace()
    {
        $this->consumer->options(null, ['new' => 'array']);
        self::assertEquals(['new' => 'array'], $this->consumer->options());
    }
}
