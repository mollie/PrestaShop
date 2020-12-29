<?php

namespace MolliePrefix;

/**
 * @author Jean Carlo Machado <contato@jeancarlomachado.com.br>
 */
class Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $this->expectExceptionMessage('message');
        throw new \Exception('message');
    }
    public function testTwo()
    {
        $this->expectExceptionCode(123);
        throw new \Exception('message', 123);
    }
}
/**
 * @author Jean Carlo Machado <contato@jeancarlomachado.com.br>
 */
\class_alias('MolliePrefix\\Test', 'Test', \false);
