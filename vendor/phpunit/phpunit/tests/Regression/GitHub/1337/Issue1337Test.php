<?php

namespace MolliePrefix;

class Issue1337Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testProvider($a)
    {
        $this->assertTrue($a);
    }
    public function dataProvider()
    {
        return ['c:\\' => [\true], 0.9 => [\true]];
    }
}
\class_alias('MolliePrefix\\Issue1337Test', 'Issue1337Test', \false);
