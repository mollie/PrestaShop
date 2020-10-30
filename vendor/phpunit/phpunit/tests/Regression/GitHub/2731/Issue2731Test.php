<?php

namespace MolliePrefix;

class Issue2731Test extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testOne()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('');
        throw new \Exception('message');
    }
}
\class_alias('MolliePrefix\\Issue2731Test', 'MolliePrefix\\Issue2731Test', \false);
