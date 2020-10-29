<?php

namespace MolliePrefix;

class Issue523Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testAttributeEquals()
    {
        $this->assertAttributeEquals('foo', 'field', new \MolliePrefix\Issue523());
    }
}
\class_alias('MolliePrefix\\Issue523Test', 'MolliePrefix\\Issue523Test', \false);
class Issue523 extends \ArrayIterator
{
    protected $field = 'foo';
}
\class_alias('MolliePrefix\\Issue523', 'MolliePrefix\\Issue523', \false);
