<?php

namespace MolliePrefix\PhpParser;

/* The autoloader is already active at this point, so we only check effects here. */
class AutoloaderTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(\class_exists('MolliePrefix\\PhpParser\\NodeVisitorAbstract'));
        $this->assertFalse(\class_exists('MolliePrefix\\PHPParser_NodeVisitor_NameResolver'));
        $this->assertFalse(\class_exists('MolliePrefix\\PhpParser\\FooBar'));
        $this->assertFalse(\class_exists('MolliePrefix\\PHPParser_FooBar'));
    }
}
