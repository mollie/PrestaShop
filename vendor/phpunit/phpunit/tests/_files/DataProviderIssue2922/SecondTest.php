<?php

namespace MolliePrefix\Foo\DataProviderIssue2922;

use MolliePrefix\PHPUnit\Framework\TestCase;
// the name of the class cannot match file name - if they match all is fine
class SecondHelloWorldTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testSecond()
    {
        $this->assertTrue(\true);
    }
}
