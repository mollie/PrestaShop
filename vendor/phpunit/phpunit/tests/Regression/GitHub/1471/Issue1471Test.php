<?php

namespace MolliePrefix;

class Issue1471Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testFailure()
    {
        $this->expectOutputString('*');
        print '*';
        $this->assertTrue(\false);
    }
}
\class_alias('MolliePrefix\\Issue1471Test', 'Issue1471Test', \false);
