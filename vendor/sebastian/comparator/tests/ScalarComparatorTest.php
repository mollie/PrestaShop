<?php

/*
 * This file is part of the Comparator package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\SebastianBergmann\Comparator;

/**
 * @coversDefaultClass SebastianBergmann\Comparator\ScalarComparator
 *
 */
class ScalarComparatorTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    private $comparator;
    protected function setUp()
    {
        $this->comparator = new \MolliePrefix\SebastianBergmann\Comparator\ScalarComparator();
    }
    public function acceptsSucceedsProvider()
    {
        return array(array("string", "string"), array(new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString(), "string"), array("string", new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString()), array("string", null), array(\false, "string"), array(\false, \true), array(null, \false), array(null, null), array("10", 10), array("", \false), array("1", \true), array(1, \true), array(0, \false), array(0.1, "0.1"));
    }
    public function acceptsFailsProvider()
    {
        return array(array(array(), array()), array("string", array()), array(new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString(), new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString()), array(\false, new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString()), array(\tmpfile(), \tmpfile()));
    }
    public function assertEqualsSucceedsProvider()
    {
        return array(array("string", "string"), array(new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString(), new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString()), array("string representation", new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString()), array(new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString(), "string representation"), array("string", "STRING", \true), array("STRING", "string", \true), array("String Representation", new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString(), \true), array(new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString(), "String Representation", \true), array("10", 10), array("", \false), array("1", \true), array(1, \true), array(0, \false), array(0.1, "0.1"), array(\false, null), array(\false, \false), array(\true, \true), array(null, null));
    }
    public function assertEqualsFailsProvider()
    {
        $stringException = 'Failed asserting that two strings are equal.';
        $otherException = 'matches expected';
        return array(
            array("string", "other string", $stringException),
            array("string", "STRING", $stringException),
            array("STRING", "string", $stringException),
            array("string", "other string", $stringException),
            // https://github.com/sebastianbergmann/phpunit/issues/1023
            array('9E6666666', '9E7777777', $stringException),
            array(new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString(), "does not match", $otherException),
            array("does not match", new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString(), $otherException),
            array(0, 'Foobar', $otherException),
            array('Foobar', 0, $otherException),
            array("10", 25, $otherException),
            array("1", \false, $otherException),
            array("", \true, $otherException),
            array(\false, \true, $otherException),
            array(\true, \false, $otherException),
            array(null, \true, $otherException),
            array(0, \true, $otherException),
        );
    }
    /**
     * @covers       ::accepts
     * @dataProvider acceptsSucceedsProvider
     */
    public function testAcceptsSucceeds($expected, $actual)
    {
        $this->assertTrue($this->comparator->accepts($expected, $actual));
    }
    /**
     * @covers       ::accepts
     * @dataProvider acceptsFailsProvider
     */
    public function testAcceptsFails($expected, $actual)
    {
        $this->assertFalse($this->comparator->accepts($expected, $actual));
    }
    /**
     * @covers       ::assertEquals
     * @dataProvider assertEqualsSucceedsProvider
     */
    public function testAssertEqualsSucceeds($expected, $actual, $ignoreCase = \false)
    {
        $exception = null;
        try {
            $this->comparator->assertEquals($expected, $actual, 0.0, \false, $ignoreCase);
        } catch (\MolliePrefix\SebastianBergmann\Comparator\ComparisonFailure $exception) {
        }
        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }
    /**
     * @covers       ::assertEquals
     * @dataProvider assertEqualsFailsProvider
     */
    public function testAssertEqualsFails($expected, $actual, $message)
    {
        $this->setExpectedException('MolliePrefix\\SebastianBergmann\\Comparator\\ComparisonFailure', $message);
        $this->comparator->assertEquals($expected, $actual);
    }
}
