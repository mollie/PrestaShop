<?php

namespace MolliePrefix;

class Issue1330Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testTrue()
    {
        $this->assertTrue(\PHPUNIT_1330);
    }
}
\class_alias('MolliePrefix\\Issue1330Test', 'Issue1330Test', \false);
