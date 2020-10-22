<?php

namespace MolliePrefix\Foo\DataProviderIssue2833;

use MolliePrefix\PHPUnit\Framework\TestCase;
class SecondTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    const DUMMY = 'dummy';
    public function testSecond()
    {
        $this->assertTrue(\true);
    }
}
