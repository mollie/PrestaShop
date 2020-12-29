<?php

namespace MolliePrefix;

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * @since      Class available since Release 2.0.0
 * @covers     PHPUnit_Extensions_RepeatedTest
 */
class Extensions_RepeatedTestTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    protected $suite;
    public function __construct()
    {
        $this->suite = new \MolliePrefix\PHPUnit_Framework_TestSuite();
        $this->suite->addTest(new \MolliePrefix\Success());
        $this->suite->addTest(new \MolliePrefix\Success());
    }
    public function testRepeatedOnce()
    {
        $test = new \MolliePrefix\PHPUnit_Extensions_RepeatedTest($this->suite, 1);
        $this->assertCount(2, $test);
        $result = $test->run();
        $this->assertCount(2, $result);
    }
    public function testRepeatedMoreThanOnce()
    {
        $test = new \MolliePrefix\PHPUnit_Extensions_RepeatedTest($this->suite, 3);
        $this->assertCount(6, $test);
        $result = $test->run();
        $this->assertCount(6, $result);
    }
    public function testRepeatedZero()
    {
        $test = new \MolliePrefix\PHPUnit_Extensions_RepeatedTest($this->suite, 0);
        $this->assertCount(0, $test);
        $result = $test->run();
        $this->assertCount(0, $result);
    }
    public function testRepeatedNegative()
    {
        try {
            $test = new \MolliePrefix\PHPUnit_Extensions_RepeatedTest($this->suite, -1);
        } catch (\Exception $e) {
            return;
        }
        $this->fail('Should throw an Exception');
    }
}
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * @since      Class available since Release 2.0.0
 * @covers     PHPUnit_Extensions_RepeatedTest
 */
\class_alias('MolliePrefix\\Extensions_RepeatedTestTest', 'Extensions_RepeatedTestTest', \false);
