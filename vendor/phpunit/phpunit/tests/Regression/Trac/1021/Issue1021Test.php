<?php

namespace MolliePrefix;

class Issue1021Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testSomething($data)
    {
        $this->assertTrue($data);
    }
    /**
     * @depends testSomething
     */
    public function testSomethingElse()
    {
    }
    public function provider()
    {
        return [[\true]];
    }
}
\class_alias('MolliePrefix\\Issue1021Test', 'MolliePrefix\\Issue1021Test', \false);
