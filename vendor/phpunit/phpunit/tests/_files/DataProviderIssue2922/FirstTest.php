<?php

namespace MolliePrefix\Foo\DataProviderIssue2922;

use MolliePrefix\PHPUnit\Framework\TestCase;
/**
 * @group foo
 */
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
        throw new \Exception();
    }
}
