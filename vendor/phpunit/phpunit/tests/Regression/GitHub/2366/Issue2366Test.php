<?php

namespace MolliePrefix;

class Issue2366
{
    public function foo()
    {
    }
}
\class_alias('MolliePrefix\\Issue2366', 'MolliePrefix\\Issue2366', \false);
class Issue2366Test extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provider
     */
    public function testOne($o)
    {
        $this->assertEquals(1, $o->foo());
    }
    public function provider()
    {
        $o = $this->createMock(\MolliePrefix\Issue2366::class);
        $o->method('foo')->willReturn(1);
        return [[$o], [$o]];
    }
}
\class_alias('MolliePrefix\\Issue2366Test', 'MolliePrefix\\Issue2366Test', \false);
