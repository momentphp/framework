<?php

namespace momentphp\tests\cases\traits;

use momentphp\traits\ClassTrait;
use PHPUnit\Framework\TestCase;

class ClassTraitConsumer
{
    use ClassTrait;
}

class Consumer
{
    use ClassTrait;
}

class ClassTraitTest extends TestCase
{
    public $consumer;
    public $consumerShort;

    public function setUp(): void
    {
        $this->consumer = new ClassTraitConsumer;
        $this->consumerShort = new Consumer;
    }

    public function tearDown(): void
    {
        unset($this->consumer, $this->consumerShort);
    }

    public function testClassNamespace()
    {
        $consumer = $this->consumer;
        self::assertEquals('momentphp\\tests\\cases\\traits', $consumer::classNamespace());
        self::assertEquals('momentphp\\tests\\cases\\traits\\foobar', $consumer::classNamespace('foobar'));
    }

    public function testClassPath()
    {
        $consumer = $this->consumer;
        self::assertEquals(__DIR__, $consumer::classPath());
        self::assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'foobar', $consumer::classPath('foobar'));
    }

    public function testClassPrefix()
    {
        $consumer = $this->consumer;
        $consumerShort = $this->consumerShort;
        self::assertEquals('ClassTrait', $consumer::classPrefix());
        self::assertEquals('classTraits', $consumer::classPrefix(true));
        self::assertEquals('', $consumerShort::classPrefix());
    }

    public function testClassSuffix()
    {
        $consumer = $this->consumer;
        $consumerShort = $this->consumerShort;
        self::assertEquals('Consumer', $consumer::classSuffix());
        self::assertEquals('consumers', $consumer::classSuffix(true));
        self::assertEquals('Consumer', $consumerShort::classSuffix());
    }

    public function testClassConfigKey()
    {
        $consumer = $this->consumer;
        self::assertEquals('tests.cases.traits.ClassTrait', $consumer::classConfigKey());
    }
}
