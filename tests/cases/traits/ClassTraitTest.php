<?php

namespace momentphp\tests\cases\traits;

class ClassTraitConsumer
{
    use \momentphp\traits\ClassTrait;
}

class Consumer
{
    use \momentphp\traits\ClassTrait;
}

class ClassTraitTest extends \PHPUnit_Framework_TestCase
{
    public $consumer;
    public $consumerShort;

    public function setUp()
    {
        $this->consumer = new ClassTraitConsumer;
        $this->consumerShort = new Consumer;
    }

    public function tearDown()
    {
        unset($this->consumer, $this->consumerShort);
    }

    public function testClassNamespace()
    {
        $consumer = $this->consumer;
        $this->assertEquals('momentphp\\tests\\cases\\traits', $consumer::classNamespace());
        $this->assertEquals('momentphp\\tests\\cases\\traits\\foobar', $consumer::classNamespace('foobar'));
    }

    public function testClassPath()
    {
        $consumer = $this->consumer;
        $this->assertEquals(__DIR__, $consumer::classPath());
        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'foobar', $consumer::classPath('foobar'));
    }

    public function testClassPrefix()
    {
        $consumer = $this->consumer;
        $consumerShort = $this->consumerShort;
        $this->assertEquals('ClassTrait', $consumer::classPrefix());
        $this->assertEquals('classTraits', $consumer::classPrefix(true));
        $this->assertEquals('', $consumerShort::classPrefix());
    }

    public function testClassSuffix()
    {
        $consumer = $this->consumer;
        $consumerShort = $this->consumerShort;
        $this->assertEquals('Consumer', $consumer::classSuffix());
        $this->assertEquals('consumers', $consumer::classSuffix(true));
        $this->assertEquals('Consumer', $consumerShort::classSuffix());
    }

    public function testClassConfigKey()
    {
        $consumer = $this->consumer;
        $this->assertEquals('tests.cases.traits.ClassTrait', $consumer::classConfigKey());
    }
}
