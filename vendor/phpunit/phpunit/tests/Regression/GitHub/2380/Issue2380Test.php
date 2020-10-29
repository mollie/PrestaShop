<?php

namespace MolliePrefix;

use MolliePrefix\PHPUnit\Framework\TestCase;
class Issue2380Test extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider generatorData
     */
    public function testGeneratorProvider($data)
    {
        $this->assertNotEmpty($data);
    }
    /**
     * @return Generator
     */
    public function generatorData()
    {
        (yield ['testing']);
    }
}
\class_alias('MolliePrefix\\Issue2380Test', 'MolliePrefix\\Issue2380Test', \false);
