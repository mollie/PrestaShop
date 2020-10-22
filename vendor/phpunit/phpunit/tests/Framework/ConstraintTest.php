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
class Framework_ConstraintTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testConstraintArrayHasKey()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::arrayHasKey(0);
        $this->assertFalse($constraint->evaluate([], '', \true));
        $this->assertEquals('has the key 0', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate([]);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that an array has the key 0.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintArrayHasKey2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::arrayHasKey(0);
        try {
            $constraint->evaluate([], 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that an array has the key 0.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintArrayNotHasKey()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::arrayHasKey(0));
        $this->assertFalse($constraint->evaluate([0 => 1], '', \true));
        $this->assertEquals('does not have the key 0', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate([0 => 1]);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that an array does not have the key 0.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintArrayNotHasKey2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::arrayHasKey(0));
        try {
            $constraint->evaluate([0], 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that an array does not have the key 0.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsReadable()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::isReadable();
        $this->assertFalse($constraint->evaluate('foo', '', \true));
        $this->assertEquals('is readable', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('foo');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that "foo" is readable.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsWritable()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::isWritable();
        $this->assertFalse($constraint->evaluate('foo', '', \true));
        $this->assertEquals('is writable', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('foo');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that "foo" is writable.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintDirectoryExists()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::directoryExists();
        $this->assertFalse($constraint->evaluate('foo', '', \true));
        $this->assertEquals('directory exists', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('foo');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that directory "foo" exists.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintFileExists()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::fileExists();
        $this->assertFalse($constraint->evaluate('foo', '', \true));
        $this->assertEquals('file exists', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('foo');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that file "foo" exists.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintFileExists2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::fileExists();
        try {
            $constraint->evaluate('foo', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that file "foo" exists.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintFileNotExists()
    {
        $file = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'ClassWithNonPublicAttributes.php';
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::fileExists());
        $this->assertFalse($constraint->evaluate($file, '', \true));
        $this->assertEquals('file does not exist', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate($file);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that file "{$file}" does not exist.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintFileNotExists2()
    {
        $file = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'ClassWithNonPublicAttributes.php';
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::fileExists());
        try {
            $constraint->evaluate($file, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that file "{$file}" does not exist.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintGreaterThan()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::greaterThan(1);
        $this->assertFalse($constraint->evaluate(0, '', \true));
        $this->assertTrue($constraint->evaluate(2, '', \true));
        $this->assertEquals('is greater than 1', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(0);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 0 is greater than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintGreaterThan2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::greaterThan(1);
        try {
            $constraint->evaluate(0, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 0 is greater than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintNotGreaterThan()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::greaterThan(1));
        $this->assertTrue($constraint->evaluate(1, '', \true));
        $this->assertEquals('is not greater than 1', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(2);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 2 is not greater than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintNotGreaterThan2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::greaterThan(1));
        try {
            $constraint->evaluate(2, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 2 is not greater than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintGreaterThanOrEqual()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::greaterThanOrEqual(1);
        $this->assertTrue($constraint->evaluate(1, '', \true));
        $this->assertFalse($constraint->evaluate(0, '', \true));
        $this->assertEquals('is equal to 1 or is greater than 1', $constraint->toString());
        $this->assertCount(2, $constraint);
        try {
            $constraint->evaluate(0);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 0 is equal to 1 or is greater than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintGreaterThanOrEqual2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::greaterThanOrEqual(1);
        try {
            $constraint->evaluate(0, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 0 is equal to 1 or is greater than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintNotGreaterThanOrEqual()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::greaterThanOrEqual(1));
        $this->assertFalse($constraint->evaluate(1, '', \true));
        $this->assertEquals('not( is equal to 1 or is greater than 1 )', $constraint->toString());
        $this->assertCount(2, $constraint);
        try {
            $constraint->evaluate(1);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that not( 1 is equal to 1 or is greater than 1 ).

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintNotGreaterThanOrEqual2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::greaterThanOrEqual(1));
        try {
            $constraint->evaluate(1, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that not( 1 is equal to 1 or is greater than 1 ).

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsAnything()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::anything();
        $this->assertTrue($constraint->evaluate(null, '', \true));
        $this->assertNull($constraint->evaluate(null));
        $this->assertEquals('is anything', $constraint->toString());
        $this->assertCount(0, $constraint);
    }
    public function testConstraintNotIsAnything()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::anything());
        $this->assertFalse($constraint->evaluate(null, '', \true));
        $this->assertEquals('is not anything', $constraint->toString());
        $this->assertCount(0, $constraint);
        try {
            $constraint->evaluate(null);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that null is not anything.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsEqual()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::equalTo(1);
        $this->assertTrue($constraint->evaluate(1, '', \true));
        $this->assertFalse($constraint->evaluate(0, '', \true));
        $this->assertEquals('is equal to 1', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(0);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 0 matches expected 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function isEqualProvider()
    {
        $a = new \stdClass();
        $a->foo = 'bar';
        $b = new \stdClass();
        $ahash = \spl_object_hash($a);
        $bhash = \spl_object_hash($b);
        $c = new \stdClass();
        $c->foo = 'bar';
        $c->int = 1;
        $c->array = [0, [1], [2], 3];
        $c->related = new \stdClass();
        $c->related->foo = "a\nb\nc\nd\ne\nf\ng\nh\ni\nj\nk";
        $c->self = $c;
        $c->c = $c;
        $d = new \stdClass();
        $d->foo = 'bar';
        $d->int = 2;
        $d->array = [0, [4], [2], 3];
        $d->related = new \stdClass();
        $d->related->foo = "a\np\nc\nd\ne\nf\ng\nh\ni\nw\nk";
        $d->self = $d;
        $d->c = $c;
        $storage1 = new \SplObjectStorage();
        $storage1->attach($a);
        $storage1->attach($b);
        $storage2 = new \SplObjectStorage();
        $storage2->attach($b);
        $storage1hash = \spl_object_hash($storage1);
        $storage2hash = \spl_object_hash($storage2);
        $dom1 = new \DOMDocument();
        $dom1->preserveWhiteSpace = \false;
        $dom1->loadXML('<root></root>');
        $dom2 = new \DOMDocument();
        $dom2->preserveWhiteSpace = \false;
        $dom2->loadXML('<root><foo/></root>');
        $data = [[1, 0, <<<EOF
Failed asserting that 0 matches expected 1.

EOF
], [1.1, 0, <<<EOF
Failed asserting that 0 matches expected 1.1.

EOF
], ['a', 'b', <<<EOF
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'a'
+'b'

EOF
], ["a\nb\nc\nd\ne\nf\ng\nh\ni\nj\nk", "a\np\nc\nd\ne\nf\ng\nh\ni\nw\nk", <<<EOF
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
 'a
-b
+p
@@ @@
 i
-j
+w
 k'

EOF
], [1, [0], <<<EOF
Array (...) does not match expected type "integer".

EOF
], [[0], 1, <<<EOF
1 does not match expected type "array".

EOF
], [[0], [1], <<<EOF
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    0 => 0
+    0 => 1
 )

EOF
], [[\true], ['true'], <<<EOF
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    0 => true
+    0 => 'true'
 )

EOF
], [[0, [1], [2], 3], [0, [4], [2], 3], <<<EOF
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
     0 => 0
     1 => Array (
-        0 => 1
+        0 => 4
     )
     2 => Array (...)
     3 => 3
 )

EOF
], [$a, [0], <<<EOF
Array (...) does not match expected type "object".

EOF
], [[0], $a, <<<EOF
stdClass Object (...) does not match expected type "array".

EOF
], [$a, $b, <<<EOF
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 stdClass Object (
-    'foo' => 'bar'
 )

EOF
], [$c, $d, <<<EOF
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 stdClass Object (
     'foo' => 'bar'
-    'int' => 1
+    'int' => 2
     'array' => Array (
         0 => 0
         1 => Array (
-            0 => 1
+            0 => 4
@@ @@
         'foo' => 'a
-        b
+        p
@@ @@
         i
-        j
+        w
         k'
     )
     'self' => stdClass Object (...)
     'c' => stdClass Object (...)
 )

EOF
], [$dom1, $dom2, <<<EOF
Failed asserting that two DOM documents are equal.
--- Expected
+++ Actual
@@ @@
 <?xml version="1.0"?>
-<root/>
+<root>
+  <foo/>
+</root>

EOF
], [new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')), new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/Chicago')), <<<EOF
Failed asserting that two DateTime objects are equal.
--- Expected
+++ Actual
@@ @@
-2013-03-29T04:13:35.000000-0400
+2013-03-29T04:13:35.000000-0500

EOF
]];
        if (\PHP_MAJOR_VERSION < 7) {
            $data[] = [$storage1, $storage2, <<<EOF
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
-SplObjectStorage Object &{$storage1hash} (
-    '{$ahash}' => Array &0 (
-        'obj' => stdClass Object &{$ahash} (
-            'foo' => 'bar'
-        )
-        'inf' => null
-    )
-    '{$bhash}' => Array &1 (
+SplObjectStorage Object &{$storage2hash} (
+    '{$bhash}' => Array &0 (
         'obj' => stdClass Object &{$bhash} ()
         'inf' => null
     )
 )

EOF
];
        } else {
            $data[] = [$storage1, $storage2, <<<EOF
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
-SplObjectStorage Object &{$storage1hash} (
-    '{$ahash}' => Array &0 (
-        'obj' => stdClass Object &{$ahash} (
-            'foo' => 'bar'
-        )
-        'inf' => null
-    )
-    '{$bhash}' => Array &1 (
+SplObjectStorage Object &{$storage2hash} (
+    '{$bhash}' => Array &0 (
         'obj' => stdClass Object &{$bhash} ()
         'inf' => null
     )
 )

EOF
];
        }
        return $data;
    }
    /**
     * @dataProvider isEqualProvider
     */
    public function testConstraintIsEqual2($expected, $actual, $message)
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::equalTo($expected);
        try {
            $constraint->evaluate($actual, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals("custom message\n{$message}", $this->trimnl(\MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e)));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNotEqual()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::equalTo(1));
        $this->assertTrue($constraint->evaluate(0, '', \true));
        $this->assertFalse($constraint->evaluate(1, '', \true));
        $this->assertEquals('is not equal to 1', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(1);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 1 is not equal to 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNotEqual2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::equalTo(1));
        try {
            $constraint->evaluate(1, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 1 is not equal to 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsIdentical()
    {
        $a = new \stdClass();
        $b = new \stdClass();
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::identicalTo($a);
        $this->assertFalse($constraint->evaluate($b, '', \true));
        $this->assertTrue($constraint->evaluate($a, '', \true));
        $this->assertEquals('is identical to an object of class "stdClass"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate($b);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that two variables reference the same object.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsIdentical2()
    {
        $a = new \stdClass();
        $b = new \stdClass();
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::identicalTo($a);
        try {
            $constraint->evaluate($b, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that two variables reference the same object.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsIdentical3()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::identicalTo('a');
        try {
            $constraint->evaluate('b', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-a
+b

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNotIdentical()
    {
        $a = new \stdClass();
        $b = new \stdClass();
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::identicalTo($a));
        $this->assertTrue($constraint->evaluate($b, '', \true));
        $this->assertFalse($constraint->evaluate($a, '', \true));
        $this->assertEquals('is not identical to an object of class "stdClass"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate($a);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that two variables don't reference the same object.

EOF
, $this->trimnl(\MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e)));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNotIdentical2()
    {
        $a = new \stdClass();
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::identicalTo($a));
        try {
            $constraint->evaluate($a, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that two variables don't reference the same object.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNotIdentical3()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::identicalTo('a'));
        try {
            $constraint->evaluate('a', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that two strings are not identical.

EOF
, $this->trimnl(\MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e)));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsInstanceOf()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::isInstanceOf('Exception');
        $this->assertFalse($constraint->evaluate(new \stdClass(), '', \true));
        $this->assertTrue($constraint->evaluate(new \Exception(), '', \true));
        $this->assertEquals('is instance of class "Exception"', $constraint->toString());
        $this->assertCount(1, $constraint);
        $interfaceConstraint = \MolliePrefix\PHPUnit_Framework_Assert::isInstanceOf('Countable');
        $this->assertFalse($interfaceConstraint->evaluate(new \stdClass(), '', \true));
        $this->assertTrue($interfaceConstraint->evaluate(new \ArrayObject(), '', \true));
        $this->assertEquals('is instance of interface "Countable"', $interfaceConstraint->toString());
        try {
            $constraint->evaluate(new \stdClass());
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that stdClass Object () is an instance of class "Exception".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsInstanceOf2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::isInstanceOf('Exception');
        try {
            $constraint->evaluate(new \stdClass(), 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that stdClass Object () is an instance of class "Exception".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNotInstanceOf()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::isInstanceOf('stdClass'));
        $this->assertFalse($constraint->evaluate(new \stdClass(), '', \true));
        $this->assertTrue($constraint->evaluate(new \Exception(), '', \true));
        $this->assertEquals('is not instance of class "stdClass"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(new \stdClass());
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that stdClass Object () is not an instance of class "stdClass".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNotInstanceOf2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::isInstanceOf('stdClass'));
        try {
            $constraint->evaluate(new \stdClass(), 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that stdClass Object () is not an instance of class "stdClass".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsType()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::isType('string');
        $this->assertFalse($constraint->evaluate(0, '', \true));
        $this->assertTrue($constraint->evaluate('', '', \true));
        $this->assertEquals('is of type "string"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(new \stdClass());
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(<<<EOF
Failed asserting that stdClass Object &%x () is of type "string".

EOF
, $this->trimnl(\MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e)));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsType2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::isType('string');
        try {
            $constraint->evaluate(new \stdClass(), 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(<<<EOF
custom message
Failed asserting that stdClass Object &%x () is of type "string".

EOF
, $this->trimnl(\MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e)));
            return;
        }
        $this->fail();
    }
    public function resources()
    {
        $fh = \fopen(__FILE__, 'r');
        \fclose($fh);
        return ['open resource' => [\fopen(__FILE__, 'r')], 'closed resource' => [$fh]];
    }
    /**
     * @dataProvider resources
     */
    public function testConstraintIsResourceTypeEvaluatesCorrectlyWithResources($resource)
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::isType('resource');
        $this->assertTrue($constraint->evaluate($resource, '', \true));
        @\fclose($resource);
    }
    public function testConstraintIsNotType()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::isType('string'));
        $this->assertTrue($constraint->evaluate(0, '', \true));
        $this->assertFalse($constraint->evaluate('', '', \true));
        $this->assertEquals('is not of type "string"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that '' is not of type "string".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNotType2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::isType('string'));
        try {
            $constraint->evaluate('', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that '' is not of type "string".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNull()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::isNull();
        $this->assertFalse($constraint->evaluate(0, '', \true));
        $this->assertTrue($constraint->evaluate(null, '', \true));
        $this->assertEquals('is null', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(0);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 0 is null.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNull2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::isNull();
        try {
            $constraint->evaluate(0, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 0 is null.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNotNull()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::isNull());
        $this->assertFalse($constraint->evaluate(null, '', \true));
        $this->assertTrue($constraint->evaluate(0, '', \true));
        $this->assertEquals('is not null', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(null);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that null is not null.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNotNull2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::isNull());
        try {
            $constraint->evaluate(null, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that null is not null.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintLessThan()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::lessThan(1);
        $this->assertTrue($constraint->evaluate(0, '', \true));
        $this->assertFalse($constraint->evaluate(1, '', \true));
        $this->assertEquals('is less than 1', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(1);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 1 is less than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintLessThan2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::lessThan(1);
        try {
            $constraint->evaluate(1, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 1 is less than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintNotLessThan()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::lessThan(1));
        $this->assertTrue($constraint->evaluate(1, '', \true));
        $this->assertFalse($constraint->evaluate(0, '', \true));
        $this->assertEquals('is not less than 1', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(0);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 0 is not less than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintNotLessThan2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::lessThan(1));
        try {
            $constraint->evaluate(0, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 0 is not less than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintLessThanOrEqual()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::lessThanOrEqual(1);
        $this->assertTrue($constraint->evaluate(1, '', \true));
        $this->assertFalse($constraint->evaluate(2, '', \true));
        $this->assertEquals('is equal to 1 or is less than 1', $constraint->toString());
        $this->assertCount(2, $constraint);
        try {
            $constraint->evaluate(2);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 2 is equal to 1 or is less than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintCallback()
    {
        $closureReflect = function ($parameter) {
            return $parameter;
        };
        $closureWithoutParameter = function () {
            return \true;
        };
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::callback($closureWithoutParameter);
        $this->assertTrue($constraint->evaluate('', '', \true));
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::callback($closureReflect);
        $this->assertTrue($constraint->evaluate(\true, '', \true));
        $this->assertFalse($constraint->evaluate(\false, '', \true));
        $callback = [$this, 'callbackReturningTrue'];
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::callback($callback);
        $this->assertTrue($constraint->evaluate(\false, '', \true));
        $callback = ['Framework_ConstraintTest', 'staticCallbackReturningTrue'];
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::callback($callback);
        $this->assertTrue($constraint->evaluate(null, '', \true));
        $this->assertEquals('is accepted by specified callback', $constraint->toString());
    }
    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting that 'This fails' is accepted by specified callback.
     */
    public function testConstraintCallbackFailure()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::callback(function () {
            return \false;
        });
        $constraint->evaluate('This fails');
    }
    public function callbackReturningTrue()
    {
        return \true;
    }
    public static function staticCallbackReturningTrue()
    {
        return \true;
    }
    public function testConstraintLessThanOrEqual2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::lessThanOrEqual(1);
        try {
            $constraint->evaluate(2, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 2 is equal to 1 or is less than 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintNotLessThanOrEqual()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::lessThanOrEqual(1));
        $this->assertTrue($constraint->evaluate(2, '', \true));
        $this->assertFalse($constraint->evaluate(1, '', \true));
        $this->assertEquals('not( is equal to 1 or is less than 1 )', $constraint->toString());
        $this->assertCount(2, $constraint);
        try {
            $constraint->evaluate(1);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that not( 1 is equal to 1 or is less than 1 ).

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintNotLessThanOrEqual2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::lessThanOrEqual(1));
        try {
            $constraint->evaluate(1, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that not( 1 is equal to 1 or is less than 1 ).

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintClassHasAttribute()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::classHasAttribute('privateAttribute');
        $this->assertTrue($constraint->evaluate('ClassWithNonPublicAttributes', '', \true));
        $this->assertFalse($constraint->evaluate('stdClass', '', \true));
        $this->assertEquals('has attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('stdClass');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that class "stdClass" has attribute "privateAttribute".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintClassHasAttribute2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::classHasAttribute('privateAttribute');
        try {
            $constraint->evaluate('stdClass', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that class "stdClass" has attribute "privateAttribute".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintClassNotHasAttribute()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::classHasAttribute('privateAttribute'));
        $this->assertTrue($constraint->evaluate('stdClass', '', \true));
        $this->assertFalse($constraint->evaluate('ClassWithNonPublicAttributes', '', \true));
        $this->assertEquals('does not have attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('ClassWithNonPublicAttributes');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintClassNotHasAttribute2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::classHasAttribute('privateAttribute'));
        try {
            $constraint->evaluate('ClassWithNonPublicAttributes', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintClassHasStaticAttribute()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::classHasStaticAttribute('privateStaticAttribute');
        $this->assertTrue($constraint->evaluate('ClassWithNonPublicAttributes', '', \true));
        $this->assertFalse($constraint->evaluate('stdClass', '', \true));
        $this->assertEquals('has static attribute "privateStaticAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('stdClass');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that class "stdClass" has static attribute "privateStaticAttribute".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintClassHasStaticAttribute2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::classHasStaticAttribute('foo');
        try {
            $constraint->evaluate('stdClass', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that class "stdClass" has static attribute "foo".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintClassNotHasStaticAttribute()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::classHasStaticAttribute('privateStaticAttribute'));
        $this->assertTrue($constraint->evaluate('stdClass', '', \true));
        $this->assertFalse($constraint->evaluate('ClassWithNonPublicAttributes', '', \true));
        $this->assertEquals('does not have static attribute "privateStaticAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('ClassWithNonPublicAttributes');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that class "ClassWithNonPublicAttributes" does not have static attribute "privateStaticAttribute".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintClassNotHasStaticAttribute2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::classHasStaticAttribute('privateStaticAttribute'));
        try {
            $constraint->evaluate('ClassWithNonPublicAttributes', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that class "ClassWithNonPublicAttributes" does not have static attribute "privateStaticAttribute".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintObjectHasAttribute()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::objectHasAttribute('privateAttribute');
        $this->assertTrue($constraint->evaluate(new \MolliePrefix\ClassWithNonPublicAttributes(), '', \true));
        $this->assertFalse($constraint->evaluate(new \stdClass(), '', \true));
        $this->assertEquals('has attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(new \stdClass());
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that object of class "stdClass" has attribute "privateAttribute".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintObjectHasAttribute2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::objectHasAttribute('privateAttribute');
        try {
            $constraint->evaluate(new \stdClass(), 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that object of class "stdClass" has attribute "privateAttribute".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintObjectNotHasAttribute()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::objectHasAttribute('privateAttribute'));
        $this->assertTrue($constraint->evaluate(new \stdClass(), '', \true));
        $this->assertFalse($constraint->evaluate(new \MolliePrefix\ClassWithNonPublicAttributes(), '', \true));
        $this->assertEquals('does not have attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(new \MolliePrefix\ClassWithNonPublicAttributes());
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that object of class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintObjectNotHasAttribute2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::objectHasAttribute('privateAttribute'));
        try {
            $constraint->evaluate(new \MolliePrefix\ClassWithNonPublicAttributes(), 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that object of class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintPCREMatch()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::matchesRegularExpression('/foo/');
        $this->assertFalse($constraint->evaluate('barbazbar', '', \true));
        $this->assertTrue($constraint->evaluate('barfoobar', '', \true));
        $this->assertEquals('matches PCRE pattern "/foo/"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('barbazbar');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'barbazbar' matches PCRE pattern "/foo/".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintPCREMatch2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::matchesRegularExpression('/foo/');
        try {
            $constraint->evaluate('barbazbar', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 'barbazbar' matches PCRE pattern "/foo/".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintPCRENotMatch()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::matchesRegularExpression('/foo/'));
        $this->assertTrue($constraint->evaluate('barbazbar', '', \true));
        $this->assertFalse($constraint->evaluate('barfoobar', '', \true));
        $this->assertEquals('does not match PCRE pattern "/foo/"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('barfoobar');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'barfoobar' does not match PCRE pattern "/foo/".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintPCRENotMatch2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::matchesRegularExpression('/foo/'));
        try {
            $constraint->evaluate('barfoobar', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 'barfoobar' does not match PCRE pattern "/foo/".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringMatches()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::matches('*%c*');
        $this->assertFalse($constraint->evaluate('**', '', \true));
        $this->assertTrue($constraint->evaluate('***', '', \true));
        $this->assertEquals('matches PCRE pattern "/^\\*.\\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }
    public function testConstraintStringMatches2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::matches('*%s*');
        $this->assertFalse($constraint->evaluate('**', '', \true));
        $this->assertTrue($constraint->evaluate('***', '', \true));
        $this->assertEquals('matches PCRE pattern "/^\\*[^\\r\\n]+\\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }
    public function testConstraintStringMatches3()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::matches('*%i*');
        $this->assertFalse($constraint->evaluate('**', '', \true));
        $this->assertTrue($constraint->evaluate('*0*', '', \true));
        $this->assertEquals('matches PCRE pattern "/^\\*[+-]?\\d+\\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }
    public function testConstraintStringMatches4()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::matches('*%d*');
        $this->assertFalse($constraint->evaluate('**', '', \true));
        $this->assertTrue($constraint->evaluate('*0*', '', \true));
        $this->assertEquals('matches PCRE pattern "/^\\*\\d+\\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }
    public function testConstraintStringMatches5()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::matches('*%x*');
        $this->assertFalse($constraint->evaluate('**', '', \true));
        $this->assertTrue($constraint->evaluate('*0f0f0f*', '', \true));
        $this->assertEquals('matches PCRE pattern "/^\\*[0-9a-fA-F]+\\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }
    public function testConstraintStringMatches6()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::matches('*%f*');
        $this->assertFalse($constraint->evaluate('**', '', \true));
        $this->assertTrue($constraint->evaluate('*1.0*', '', \true));
        $this->assertEquals('matches PCRE pattern "/^\\*[+-]?\\.?\\d+\\.?\\d*(?:[Ee][+-]?\\d+)?\\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }
    public function testConstraintStringStartsWith()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::stringStartsWith('prefix');
        $this->assertFalse($constraint->evaluate('foo', '', \true));
        $this->assertTrue($constraint->evaluate('prefixfoo', '', \true));
        $this->assertEquals('starts with "prefix"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('foo');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'foo' starts with "prefix".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringStartsWith2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::stringStartsWith('prefix');
        try {
            $constraint->evaluate('foo', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 'foo' starts with "prefix".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringStartsNotWith()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::stringStartsWith('prefix'));
        $this->assertTrue($constraint->evaluate('foo', '', \true));
        $this->assertFalse($constraint->evaluate('prefixfoo', '', \true));
        $this->assertEquals('starts not with "prefix"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('prefixfoo');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'prefixfoo' starts not with "prefix".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringStartsNotWith2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::stringStartsWith('prefix'));
        try {
            $constraint->evaluate('prefixfoo', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 'prefixfoo' starts not with "prefix".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringContains()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::stringContains('foo');
        $this->assertFalse($constraint->evaluate('barbazbar', '', \true));
        $this->assertTrue($constraint->evaluate('barfoobar', '', \true));
        $this->assertEquals('contains "foo"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('barbazbar');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'barbazbar' contains "foo".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringContainsWhenIgnoreCase()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::stringContains('oryginał', \true);
        $this->assertFalse($constraint->evaluate('oryginal', '', \true));
        $this->assertTrue($constraint->evaluate('ORYGINAŁ', '', \true));
        $this->assertTrue($constraint->evaluate('oryginał', '', \true));
        $this->assertEquals('contains "oryginał"', $constraint->toString());
        $this->assertEquals(1, \count($constraint));
        try {
            $constraint->evaluate('oryginal');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'oryginal' contains "oryginał".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringContainsForUtf8StringWhenNotIgnoreCase()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::stringContains('oryginał', \false);
        $this->assertFalse($constraint->evaluate('oryginal', '', \true));
        $this->assertFalse($constraint->evaluate('ORYGINAŁ', '', \true));
        $this->assertTrue($constraint->evaluate('oryginał', '', \true));
        $this->assertEquals('contains "oryginał"', $constraint->toString());
        $this->assertEquals(1, \count($constraint));
        try {
            $constraint->evaluate('oryginal');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'oryginal' contains "oryginał".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringContains2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::stringContains('foo');
        try {
            $constraint->evaluate('barbazbar', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 'barbazbar' contains "foo".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringNotContains()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::stringContains('foo'));
        $this->assertTrue($constraint->evaluate('barbazbar', '', \true));
        $this->assertFalse($constraint->evaluate('barfoobar', '', \true));
        $this->assertEquals('does not contain "foo"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('barfoobar');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'barfoobar' does not contain "foo".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringNotContainsWhenIgnoreCase()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::stringContains('oryginał'));
        $this->assertTrue($constraint->evaluate('original', '', \true));
        $this->assertFalse($constraint->evaluate('ORYGINAŁ', '', \true));
        $this->assertFalse($constraint->evaluate('oryginał', '', \true));
        $this->assertEquals('does not contain "oryginał"', $constraint->toString());
        $this->assertEquals(1, \count($constraint));
        try {
            $constraint->evaluate('ORYGINAŁ');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'ORYGINAŁ' does not contain "oryginał".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringNotContainsForUtf8StringWhenNotIgnoreCase()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::stringContains('oryginał', \false));
        $this->assertTrue($constraint->evaluate('original', '', \true));
        $this->assertTrue($constraint->evaluate('ORYGINAŁ', '', \true));
        $this->assertFalse($constraint->evaluate('oryginał', '', \true));
        $this->assertEquals('does not contain "oryginał"', $constraint->toString());
        $this->assertEquals(1, \count($constraint));
        try {
            $constraint->evaluate('oryginał');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'oryginał' does not contain "oryginał".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringNotContains2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::stringContains('foo'));
        try {
            $constraint->evaluate('barfoobar', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 'barfoobar' does not contain "foo".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringEndsWith()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::stringEndsWith('suffix');
        $this->assertFalse($constraint->evaluate('foo', '', \true));
        $this->assertTrue($constraint->evaluate('foosuffix', '', \true));
        $this->assertEquals('ends with "suffix"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('foo');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'foo' ends with "suffix".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringEndsWith2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::stringEndsWith('suffix');
        try {
            $constraint->evaluate('foo', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 'foo' ends with "suffix".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringEndsNotWith()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::stringEndsWith('suffix'));
        $this->assertTrue($constraint->evaluate('foo', '', \true));
        $this->assertFalse($constraint->evaluate('foosuffix', '', \true));
        $this->assertEquals('ends not with "suffix"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('foosuffix');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that 'foosuffix' ends not with "suffix".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintStringEndsNotWith2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::stringEndsWith('suffix'));
        try {
            $constraint->evaluate('foosuffix', 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that 'foosuffix' ends not with "suffix".

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintArrayContainsCheckForObjectIdentity()
    {
        // Check for primitive type.
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_TraversableContains('foo', \true, \true);
        $this->assertFalse($constraint->evaluate([0], '', \true));
        $this->assertFalse($constraint->evaluate([\true], '', \true));
        // Default case.
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_TraversableContains('foo');
        $this->assertTrue($constraint->evaluate([0], '', \true));
        $this->assertTrue($constraint->evaluate([\true], '', \true));
    }
    public function testConstraintArrayContains()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_TraversableContains('foo');
        $this->assertFalse($constraint->evaluate(['bar'], '', \true));
        $this->assertTrue($constraint->evaluate(['foo'], '', \true));
        $this->assertEquals("contains 'foo'", $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(['bar']);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that an array contains 'foo'.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintArrayContains2()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_TraversableContains('foo');
        try {
            $constraint->evaluate(['bar'], 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that an array contains 'foo'.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintArrayNotContains()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(new \MolliePrefix\PHPUnit_Framework_Constraint_TraversableContains('foo'));
        $this->assertTrue($constraint->evaluate(['bar'], '', \true));
        $this->assertFalse($constraint->evaluate(['foo'], '', \true));
        $this->assertEquals("does not contain 'foo'", $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(['foo']);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that an array does not contain 'foo'.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintArrayNotContains2()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(new \MolliePrefix\PHPUnit_Framework_Constraint_TraversableContains('foo'));
        try {
            $constraint->evaluate(['foo'], 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that an array does not contain 'foo'.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintSplObjectStorageContains()
    {
        $object = new \StdClass();
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_TraversableContains($object);
        $this->assertStringMatchesFormat('contains stdClass Object &%s ()', $constraint->toString());
        $storage = new \SplObjectStorage();
        $this->assertFalse($constraint->evaluate($storage, '', \true));
        $storage->attach($object);
        $this->assertTrue($constraint->evaluate($storage, '', \true));
        try {
            $constraint->evaluate(new \SplObjectStorage());
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(<<<EOF
Failed asserting that a traversable contains stdClass Object &%x ().

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintSplObjectStorageContains2()
    {
        $object = new \StdClass();
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_TraversableContains($object);
        try {
            $constraint->evaluate(new \SplObjectStorage(), 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(<<<EOF
custom message
Failed asserting that a traversable contains stdClass Object &%x ().

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testAttributeEqualTo()
    {
        $object = new \MolliePrefix\ClassWithNonPublicAttributes();
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::attributeEqualTo('foo', 1);
        $this->assertTrue($constraint->evaluate($object, '', \true));
        $this->assertEquals('attribute "foo" is equal to 1', $constraint->toString());
        $this->assertCount(1, $constraint);
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::attributeEqualTo('foo', 2);
        $this->assertFalse($constraint->evaluate($object, '', \true));
        try {
            $constraint->evaluate($object);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that attribute "foo" is equal to 2.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testAttributeEqualTo2()
    {
        $object = new \MolliePrefix\ClassWithNonPublicAttributes();
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::attributeEqualTo('foo', 2);
        try {
            $constraint->evaluate($object, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that attribute "foo" is equal to 2.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testAttributeNotEqualTo()
    {
        $object = new \MolliePrefix\ClassWithNonPublicAttributes();
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::attributeEqualTo('foo', 2));
        $this->assertTrue($constraint->evaluate($object, '', \true));
        $this->assertEquals('attribute "foo" is not equal to 2', $constraint->toString());
        $this->assertCount(1, $constraint);
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::attributeEqualTo('foo', 1));
        $this->assertFalse($constraint->evaluate($object, '', \true));
        try {
            $constraint->evaluate($object);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that attribute "foo" is not equal to 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testAttributeNotEqualTo2()
    {
        $object = new \MolliePrefix\ClassWithNonPublicAttributes();
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(\MolliePrefix\PHPUnit_Framework_Assert::attributeEqualTo('foo', 1));
        try {
            $constraint->evaluate($object, 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that attribute "foo" is not equal to 1.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsEmpty()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_IsEmpty();
        $this->assertFalse($constraint->evaluate(['foo'], '', \true));
        $this->assertTrue($constraint->evaluate([], '', \true));
        $this->assertFalse($constraint->evaluate(new \ArrayObject(['foo']), '', \true));
        $this->assertTrue($constraint->evaluate(new \ArrayObject([]), '', \true));
        $this->assertEquals('is empty', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(['foo']);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that an array is empty.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintIsEmpty2()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_IsEmpty();
        try {
            $constraint->evaluate(['foo'], 'custom message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that an array is empty.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintCountWithAnArray()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_Count(5);
        $this->assertTrue($constraint->evaluate([1, 2, 3, 4, 5], '', \true));
        $this->assertFalse($constraint->evaluate([1, 2, 3, 4], '', \true));
    }
    public function testConstraintCountWithAnIteratorWhichDoesNotImplementCountable()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_Count(5);
        $this->assertTrue($constraint->evaluate(new \MolliePrefix\TestIterator([1, 2, 3, 4, 5]), '', \true));
        $this->assertFalse($constraint->evaluate(new \MolliePrefix\TestIterator([1, 2, 3, 4]), '', \true));
    }
    public function testConstraintCountWithAnObjectImplementingCountable()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_Count(5);
        $this->assertTrue($constraint->evaluate(new \ArrayObject([1, 2, 3, 4, 5]), '', \true));
        $this->assertFalse($constraint->evaluate(new \ArrayObject([1, 2, 3, 4]), '', \true));
    }
    public function testConstraintCountFailing()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_Count(5);
        try {
            $constraint->evaluate([1, 2]);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that actual size 2 matches expected size 5.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintNotCountFailing()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(new \MolliePrefix\PHPUnit_Framework_Constraint_Count(2));
        try {
            $constraint->evaluate([1, 2]);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that actual size 2 does not match expected size 2.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintSameSizeWithAnArray()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_SameSize([1, 2, 3, 4, 5]);
        $this->assertTrue($constraint->evaluate([6, 7, 8, 9, 10], '', \true));
        $this->assertFalse($constraint->evaluate([1, 2, 3, 4], '', \true));
    }
    public function testConstraintSameSizeWithAnIteratorWhichDoesNotImplementCountable()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_SameSize(new \MolliePrefix\TestIterator([1, 2, 3, 4, 5]));
        $this->assertTrue($constraint->evaluate(new \MolliePrefix\TestIterator([6, 7, 8, 9, 10]), '', \true));
        $this->assertFalse($constraint->evaluate(new \MolliePrefix\TestIterator([1, 2, 3, 4]), '', \true));
    }
    public function testConstraintSameSizeWithAnObjectImplementingCountable()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_SameSize(new \ArrayObject([1, 2, 3, 4, 5]));
        $this->assertTrue($constraint->evaluate(new \ArrayObject([6, 7, 8, 9, 10]), '', \true));
        $this->assertFalse($constraint->evaluate(new \ArrayObject([1, 2, 3, 4]), '', \true));
    }
    public function testConstraintSameSizeFailing()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_SameSize([1, 2, 3, 4, 5]);
        try {
            $constraint->evaluate([1, 2]);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that actual size 2 matches expected size 5.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintNotSameSizeFailing()
    {
        $constraint = \MolliePrefix\PHPUnit_Framework_Assert::logicalNot(new \MolliePrefix\PHPUnit_Framework_Constraint_SameSize([1, 2]));
        try {
            $constraint->evaluate([3, 4]);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that actual size 2 does not match expected size 2.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    public function testConstraintException()
    {
        $constraint = new \MolliePrefix\PHPUnit_Framework_Constraint_Exception('FoobarException');
        $exception = new \MolliePrefix\DummyException('Test');
        $stackTrace = \MolliePrefix\PHPUnit_Util_Filter::getFilteredStacktrace($exception);
        try {
            $constraint->evaluate($exception);
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that exception of type "DummyException" matches expected exception "FoobarException". Message was: "Test" at
{$stackTrace}.

EOF
, \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
            return;
        }
        $this->fail();
    }
    /**
     * Removes spaces in front of newlines
     *
     * @param string $string
     *
     * @return string
     */
    private function trimnl($string)
    {
        return \preg_replace('/[ ]*\\n/', "\n", $string);
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
\class_alias('MolliePrefix\\Framework_ConstraintTest', 'Framework_ConstraintTest', \false);
