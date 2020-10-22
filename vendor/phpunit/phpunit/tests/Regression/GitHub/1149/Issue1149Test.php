<?php

namespace MolliePrefix;

class Issue1149Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $this->assertTrue(\true);
        print '1';
    }
    /**
     * @runInSeparateProcess
     */
    public function testTwo()
    {
        $this->assertTrue(\true);
        print '2';
    }
}
\class_alias('MolliePrefix\\Issue1149Test', 'Issue1149Test', \false);
