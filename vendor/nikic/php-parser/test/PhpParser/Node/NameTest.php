<?php

namespace MolliePrefix\PhpParser\Node;

class NameTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $name = new \MolliePrefix\PhpParser\Node\Name(array('foo', 'bar'));
        $this->assertSame(array('foo', 'bar'), $name->parts);
        $name = new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar');
        $this->assertSame(array('foo', 'bar'), $name->parts);
        $name = new \MolliePrefix\PhpParser\Node\Name($name);
        $this->assertSame(array('foo', 'bar'), $name->parts);
    }
    public function testGet()
    {
        $name = new \MolliePrefix\PhpParser\Node\Name('foo');
        $this->assertSame('foo', $name->getFirst());
        $this->assertSame('foo', $name->getLast());
        $name = new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar');
        $this->assertSame('foo', $name->getFirst());
        $this->assertSame('bar', $name->getLast());
    }
    public function testToString()
    {
        $name = new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar');
        $this->assertSame('MolliePrefix\\foo\\bar', (string) $name);
        $this->assertSame('MolliePrefix\\foo\\bar', $name->toString());
    }
    public function testSlice()
    {
        $name = new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar\\baz');
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar\\baz'), $name->slice(0));
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\bar\\baz'), $name->slice(1));
        $this->assertNull($name->slice(3));
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar\\baz'), $name->slice(-3));
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\bar\\baz'), $name->slice(-2));
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar'), $name->slice(0, -1));
        $this->assertNull($name->slice(0, -3));
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name('bar'), $name->slice(1, -1));
        $this->assertNull($name->slice(1, -2));
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name('bar'), $name->slice(-2, 1));
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name('bar'), $name->slice(-2, -1));
        $this->assertNull($name->slice(-2, -2));
    }
    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Offset 4 is out of bounds
     */
    public function testSliceOffsetTooLarge()
    {
        (new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar\\baz'))->slice(4);
    }
    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Offset -4 is out of bounds
     */
    public function testSliceOffsetTooSmall()
    {
        (new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar\\baz'))->slice(-4);
    }
    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Length 4 is out of bounds
     */
    public function testSliceLengthTooLarge()
    {
        (new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar\\baz'))->slice(0, 4);
    }
    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Length -4 is out of bounds
     */
    public function testSliceLengthTooSmall()
    {
        (new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar\\baz'))->slice(0, -4);
    }
    public function testConcat()
    {
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar\\baz'), \MolliePrefix\PhpParser\Node\Name::concat('foo', 'MolliePrefix\\bar\\baz'));
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name\FullyQualified('MolliePrefix\\foo\\bar'), \MolliePrefix\PhpParser\Node\Name\FullyQualified::concat(['foo'], new \MolliePrefix\PhpParser\Node\Name('bar')));
        $attributes = ['foo' => 'bar'];
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name\Relative('MolliePrefix\\foo\\bar\\baz', $attributes), \MolliePrefix\PhpParser\Node\Name\Relative::concat(new \MolliePrefix\PhpParser\Node\Name\FullyQualified('MolliePrefix\\foo\\bar'), 'baz', $attributes));
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name('foo'), \MolliePrefix\PhpParser\Node\Name::concat(null, 'foo'));
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Name('foo'), \MolliePrefix\PhpParser\Node\Name::concat('foo', null));
        $this->assertNull(\MolliePrefix\PhpParser\Node\Name::concat(null, null));
    }
    public function testIs()
    {
        $name = new \MolliePrefix\PhpParser\Node\Name('foo');
        $this->assertTrue($name->isUnqualified());
        $this->assertFalse($name->isQualified());
        $this->assertFalse($name->isFullyQualified());
        $this->assertFalse($name->isRelative());
        $name = new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar');
        $this->assertFalse($name->isUnqualified());
        $this->assertTrue($name->isQualified());
        $this->assertFalse($name->isFullyQualified());
        $this->assertFalse($name->isRelative());
        $name = new \MolliePrefix\PhpParser\Node\Name\FullyQualified('foo');
        $this->assertFalse($name->isUnqualified());
        $this->assertFalse($name->isQualified());
        $this->assertTrue($name->isFullyQualified());
        $this->assertFalse($name->isRelative());
        $name = new \MolliePrefix\PhpParser\Node\Name\Relative('foo');
        $this->assertFalse($name->isUnqualified());
        $this->assertFalse($name->isQualified());
        $this->assertFalse($name->isFullyQualified());
        $this->assertTrue($name->isRelative());
    }
    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Expected string, array of parts or Name instance
     */
    public function testInvalidArg()
    {
        \MolliePrefix\PhpParser\Node\Name::concat('foo', new \stdClass());
    }
}
