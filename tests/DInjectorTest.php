<?php

namespace ezsql\Tests;

use Psr\Container\ContainerInterface;
use ezsql\DInjector;
use ezsql\Tests\EZTestCase;
use ezsql\Exception\ContainerException;
use ezsql\Exception\NotFoundException;

use ezsql\Tests\ezInterface;
use ezsql\Tests\Baz;
use ezsql\Tests\Bar;
use ezsql\Tests\Foo;

class DInjectorTest extends EZTestCase
{
    public function testSet()
    {
        $container = new DInjector();
        $this->assertTrue($container instanceof ContainerInterface);
        $container->set('Baz');
        $this->assertTrue($container->has('Baz'));
    }

    public function testHas()
    {
        $container = new DInjector();
        $container->set('Test', 'Test');
        $this->assertTrue($container->has('Test'));
        $this->assertFalse($container->has('TestOther'));
    }

    public function testAutoWire()
    {
        $container = new DInjector();
        $container->set('Baz', 'Baz');
        $container->set('ezsql\Tests\ezInterface', 'ezsql\Tests\Foo');
        $baz = $container->autoWire('ezsql\Tests\Baz');
        $this->assertTrue($baz instanceof Baz);
        $this->assertTrue($baz->foo instanceof Foo);
    }

    public function testAutoWire_Exception()
    {
        $container = new DInjector();
        $this->expectException(\ReflectionException::class);
        $baz = $container->autoWire('Baz');
    }

    public function testAutoWire_Error()
    {
        $container = new DInjector();
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageRegExp('/[is not instantiable]/');
        $baz = $container->autoWire('ezsql\Tests\Baz');
    }

    public function testGet()
    {
        $container = new DInjector();
        $container->set('ezsql\Tests\Baz', 'ezsql\Tests\Baz');
        $container->set('ezsql\Tests\ezInterface', 'ezsql\Tests\Bar');
        $baz = $container->get('ezsql\Tests\Baz');
        $this->assertTrue($baz instanceof Baz);
        $this->assertTrue($baz->foo instanceof Bar);
    }

    public function testGet_Error()
    {
        $container = new DInjector();
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageRegExp('/[does not exists]/');
        $baz = $container->get('Baz');
    }
}
