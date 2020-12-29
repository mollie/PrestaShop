<?php

namespace MolliePrefix;

use MolliePrefix\PHPUnit\ExampleExtension\TestCaseTrait;
class OneTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    use TestCaseTrait;
    public function testOne()
    {
        $this->assertExampleExtensionInitialized();
    }
}
\class_alias('MolliePrefix\\OneTest', 'OneTest', \false);
