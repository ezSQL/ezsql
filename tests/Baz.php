<?php

namespace ezsql\Tests;

use ezsql\Tests\ezInterface;  

class Baz
{
    public $foo;
    public function __construct(ezInterface $foo = null)
    {
        $this->foo = $foo;
    }
}
    