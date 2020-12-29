<?php

namespace MolliePrefix;

class Issue74Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testCreateAndThrowNewExceptionInProcessIsolation()
    {
        require_once __DIR__ . '/NewException.php';
        throw new \MolliePrefix\NewException('Testing GH-74');
    }
}
\class_alias('MolliePrefix\\Issue74Test', 'Issue74Test', \false);
