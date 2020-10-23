<?php

namespace MolliePrefix\Foo\DataProviderIssue2833;

use MolliePrefix\PHPUnit\Framework\TestCase;
class FirstTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provide
     */
    public function testFirst($x)
    {
        $this->assertTrue(\true);
    }
    public function provide()
    {
        \MolliePrefix\Foo\DataProviderIssue2833\SecondTest::DUMMY;
        return [[\true]];
    }
}
