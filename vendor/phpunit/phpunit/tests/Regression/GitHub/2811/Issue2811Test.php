<?php

namespace MolliePrefix;

class Issue2811Test extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testOne()
    {
        $this->expectExceptionMessage('hello');
        throw new \Exception('hello');
    }
}
\class_alias('MolliePrefix\\Issue2811Test', 'Issue2811Test', \false);
