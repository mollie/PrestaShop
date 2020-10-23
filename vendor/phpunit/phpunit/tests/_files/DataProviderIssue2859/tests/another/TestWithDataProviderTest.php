<?php

namespace MolliePrefix\Foo\DataProviderIssue2859;

use MolliePrefix\PHPUnit\Framework\TestCase;
class TestWithDataProviderTest extends \MolliePrefix\PHPUnit\Framework\TestCase
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
        return [[\true]];
    }
}
