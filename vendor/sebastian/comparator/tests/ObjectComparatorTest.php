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

use stdClass;
/**
 * @coversDefaultClass SebastianBergmann\Comparator\ObjectComparator
 *
 */
class ObjectComparatorTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    private $comparator;
    protected function setUp()
    {
        $this->comparator = new \MolliePrefix\SebastianBergmann\Comparator\ObjectComparator();
        $this->comparator->setFactory(new \MolliePrefix\SebastianBergmann\Comparator\Factory());
    }
    public function acceptsSucceedsProvider()
    {
        return array(array(new \MolliePrefix\SebastianBergmann\Comparator\TestClass(), new \MolliePrefix\SebastianBergmann\Comparator\TestClass()), array(new \stdClass(), new \stdClass()), array(new \stdClass(), new \MolliePrefix\SebastianBergmann\Comparator\TestClass()));
    }
    public function acceptsFailsProvider()
    {
        return array(array(new \stdClass(), null), array(null, new \stdClass()), array(null, null));
    }
    public function assertEqualsSucceedsProvider()
    {
        // cyclic dependencies
        $book1 = new \MolliePrefix\SebastianBergmann\Comparator\Book();
        $book1->author = new \MolliePrefix\SebastianBergmann\Comparator\Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2 = new \MolliePrefix\SebastianBergmann\Comparator\Book();
        $book2->author = new \MolliePrefix\SebastianBergmann\Comparator\Author('Terry Pratchett');
        $book2->author->books[] = $book2;
        $object1 = new \MolliePrefix\SebastianBergmann\Comparator\SampleClass(4, 8, 15);
        $object2 = new \MolliePrefix\SebastianBergmann\Comparator\SampleClass(4, 8, 15);
        return array(array($object1, $object1), array($object1, $object2), array($book1, $book1), array($book1, $book2), array(new \MolliePrefix\SebastianBergmann\Comparator\Struct(2.3), new \MolliePrefix\SebastianBergmann\Comparator\Struct(2.5), 0.5));
    }
    public function assertEqualsFailsProvider()
    {
        $typeMessage = 'is not instance of expected class';
        $equalMessage = 'Failed asserting that two objects are equal.';
        // cyclic dependencies
        $book1 = new \MolliePrefix\SebastianBergmann\Comparator\Book();
        $book1->author = new \MolliePrefix\SebastianBergmann\Comparator\Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2 = new \MolliePrefix\SebastianBergmann\Comparator\Book();
        $book2->author = new \MolliePrefix\SebastianBergmann\Comparator\Author('Terry Pratch');
        $book2->author->books[] = $book2;
        $book3 = new \MolliePrefix\SebastianBergmann\Comparator\Book();
        $book3->author = 'Terry Pratchett';
        $book4 = new \stdClass();
        $book4->author = 'Terry Pratchett';
        $object1 = new \MolliePrefix\SebastianBergmann\Comparator\SampleClass(4, 8, 15);
        $object2 = new \MolliePrefix\SebastianBergmann\Comparator\SampleClass(16, 23, 42);
        return array(array(new \MolliePrefix\SebastianBergmann\Comparator\SampleClass(4, 8, 15), new \MolliePrefix\SebastianBergmann\Comparator\SampleClass(16, 23, 42), $equalMessage), array($object1, $object2, $equalMessage), array($book1, $book2, $equalMessage), array($book3, $book4, $typeMessage), array(new \MolliePrefix\SebastianBergmann\Comparator\Struct(2.3), new \MolliePrefix\SebastianBergmann\Comparator\Struct(4.2), $equalMessage, 0.5));
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
    public function testAssertEqualsSucceeds($expected, $actual, $delta = 0.0)
    {
        $exception = null;
        try {
            $this->comparator->assertEquals($expected, $actual, $delta);
        } catch (\MolliePrefix\SebastianBergmann\Comparator\ComparisonFailure $exception) {
        }
        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }
    /**
     * @covers       ::assertEquals
     * @dataProvider assertEqualsFailsProvider
     */
    public function testAssertEqualsFails($expected, $actual, $message, $delta = 0.0)
    {
        $this->setExpectedException('MolliePrefix\\SebastianBergmann\\Comparator\\ComparisonFailure', $message);
        $this->comparator->assertEquals($expected, $actual, $delta);
    }
}
