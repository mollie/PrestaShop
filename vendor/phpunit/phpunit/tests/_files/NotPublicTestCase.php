<?php

namespace MolliePrefix;

class NotPublicTestCase extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testPublic()
    {
    }
    protected function testNotPublic()
    {
    }
}
\class_alias('MolliePrefix\\NotPublicTestCase', 'NotPublicTestCase', \false);
