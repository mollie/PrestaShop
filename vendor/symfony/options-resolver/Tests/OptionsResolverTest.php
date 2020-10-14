<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\OptionsResolver\Tests;

use MolliePrefix\PHPUnit\Framework\Assert;
use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use MolliePrefix\Symfony\Component\OptionsResolver\Options;
use MolliePrefix\Symfony\Component\OptionsResolver\OptionsResolver;
class OptionsResolverTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @var OptionsResolver
     */
    private $resolver;
    protected function setUp()
    {
        $this->resolver = new \MolliePrefix\Symfony\Component\OptionsResolver\OptionsResolver();
    }
    public function testResolveFailsIfNonExistingOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\UndefinedOptionsException');
        $this->expectExceptionMessage('The option "foo" does not exist. Defined options are: "a", "z".');
        $this->resolver->setDefault('z', '1');
        $this->resolver->setDefault('a', '2');
        $this->resolver->resolve(['foo' => 'bar']);
    }
    public function testResolveFailsIfMultipleNonExistingOptions()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\UndefinedOptionsException');
        $this->expectExceptionMessage('The options "baz", "foo", "ping" do not exist. Defined options are: "a", "z".');
        $this->resolver->setDefault('z', '1');
        $this->resolver->setDefault('a', '2');
        $this->resolver->resolve(['ping' => 'pong', 'foo' => 'bar', 'baz' => 'bam']);
    }
    public function testResolveFailsFromLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->resolve([]);
        });
        $this->resolver->resolve();
    }
    public function testSetDefaultReturnsThis()
    {
        $this->assertSame($this->resolver, $this->resolver->setDefault('foo', 'bar'));
    }
    public function testSetDefault()
    {
        $this->resolver->setDefault('one', '1');
        $this->resolver->setDefault('two', '20');
        $this->assertEquals(['one' => '1', 'two' => '20'], $this->resolver->resolve());
    }
    public function testFailIfSetDefaultFromLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('lazy', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->setDefault('default', 42);
        });
        $this->resolver->resolve();
    }
    public function testHasDefault()
    {
        $this->assertFalse($this->resolver->hasDefault('foo'));
        $this->resolver->setDefault('foo', 42);
        $this->assertTrue($this->resolver->hasDefault('foo'));
    }
    public function testHasDefaultWithNullValue()
    {
        $this->assertFalse($this->resolver->hasDefault('foo'));
        $this->resolver->setDefault('foo', null);
        $this->assertTrue($this->resolver->hasDefault('foo'));
    }
    public function testSetLazyReturnsThis()
    {
        $this->assertSame($this->resolver, $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
        }));
    }
    public function testSetLazyClosure()
    {
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            return 'lazy';
        });
        $this->assertEquals(['foo' => 'lazy'], $this->resolver->resolve());
    }
    public function testClosureWithoutTypeHintNotInvoked()
    {
        $closure = function ($options) {
            \MolliePrefix\PHPUnit\Framework\Assert::fail('Should not be called');
        };
        $this->resolver->setDefault('foo', $closure);
        $this->assertSame(['foo' => $closure], $this->resolver->resolve());
    }
    public function testClosureWithoutParametersNotInvoked()
    {
        $closure = function () {
            \MolliePrefix\PHPUnit\Framework\Assert::fail('Should not be called');
        };
        $this->resolver->setDefault('foo', $closure);
        $this->assertSame(['foo' => $closure], $this->resolver->resolve());
    }
    public function testAccessPreviousDefaultValue()
    {
        // defined by superclass
        $this->resolver->setDefault('foo', 'bar');
        // defined by subclass
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options, $previousValue) {
            \MolliePrefix\PHPUnit\Framework\Assert::assertEquals('bar', $previousValue);
            return 'lazy';
        });
        $this->assertEquals(['foo' => 'lazy'], $this->resolver->resolve());
    }
    public function testAccessPreviousLazyDefaultValue()
    {
        // defined by superclass
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            return 'bar';
        });
        // defined by subclass
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options, $previousValue) {
            \MolliePrefix\PHPUnit\Framework\Assert::assertEquals('bar', $previousValue);
            return 'lazy';
        });
        $this->assertEquals(['foo' => 'lazy'], $this->resolver->resolve());
    }
    public function testPreviousValueIsNotEvaluatedIfNoSecondArgument()
    {
        // defined by superclass
        $this->resolver->setDefault('foo', function () {
            \MolliePrefix\PHPUnit\Framework\Assert::fail('Should not be called');
        });
        // defined by subclass, no $previousValue argument defined!
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            return 'lazy';
        });
        $this->assertEquals(['foo' => 'lazy'], $this->resolver->resolve());
    }
    public function testOverwrittenLazyOptionNotEvaluated()
    {
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            \MolliePrefix\PHPUnit\Framework\Assert::fail('Should not be called');
        });
        $this->resolver->setDefault('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testInvokeEachLazyOptionOnlyOnce()
    {
        $calls = 0;
        $this->resolver->setDefault('lazy1', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) use(&$calls) {
            \MolliePrefix\PHPUnit\Framework\Assert::assertSame(1, ++$calls);
            $options['lazy2'];
        });
        $this->resolver->setDefault('lazy2', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) use(&$calls) {
            \MolliePrefix\PHPUnit\Framework\Assert::assertSame(2, ++$calls);
        });
        $this->resolver->resolve();
        $this->assertSame(2, $calls);
    }
    public function testSetRequiredReturnsThis()
    {
        $this->assertSame($this->resolver, $this->resolver->setRequired('foo'));
    }
    public function testFailIfSetRequiredFromLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->setRequired('bar');
        });
        $this->resolver->resolve();
    }
    public function testResolveFailsIfRequiredOptionMissing()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\MissingOptionsException');
        $this->resolver->setRequired('foo');
        $this->resolver->resolve();
    }
    public function testResolveSucceedsIfRequiredOptionSet()
    {
        $this->resolver->setRequired('foo');
        $this->resolver->setDefault('foo', 'bar');
        $this->assertNotEmpty($this->resolver->resolve());
    }
    public function testResolveSucceedsIfRequiredOptionPassed()
    {
        $this->resolver->setRequired('foo');
        $this->assertNotEmpty($this->resolver->resolve(['foo' => 'bar']));
    }
    public function testIsRequired()
    {
        $this->assertFalse($this->resolver->isRequired('foo'));
        $this->resolver->setRequired('foo');
        $this->assertTrue($this->resolver->isRequired('foo'));
    }
    public function testRequiredIfSetBefore()
    {
        $this->assertFalse($this->resolver->isRequired('foo'));
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setRequired('foo');
        $this->assertTrue($this->resolver->isRequired('foo'));
    }
    public function testStillRequiredAfterSet()
    {
        $this->assertFalse($this->resolver->isRequired('foo'));
        $this->resolver->setRequired('foo');
        $this->resolver->setDefault('foo', 'bar');
        $this->assertTrue($this->resolver->isRequired('foo'));
    }
    public function testIsNotRequiredAfterRemove()
    {
        $this->assertFalse($this->resolver->isRequired('foo'));
        $this->resolver->setRequired('foo');
        $this->resolver->remove('foo');
        $this->assertFalse($this->resolver->isRequired('foo'));
    }
    public function testIsNotRequiredAfterClear()
    {
        $this->assertFalse($this->resolver->isRequired('foo'));
        $this->resolver->setRequired('foo');
        $this->resolver->clear();
        $this->assertFalse($this->resolver->isRequired('foo'));
    }
    public function testGetRequiredOptions()
    {
        $this->resolver->setRequired(['foo', 'bar']);
        $this->resolver->setDefault('bam', 'baz');
        $this->resolver->setDefault('foo', 'boo');
        $this->assertSame(['foo', 'bar'], $this->resolver->getRequiredOptions());
    }
    public function testIsMissingIfNotSet()
    {
        $this->assertFalse($this->resolver->isMissing('foo'));
        $this->resolver->setRequired('foo');
        $this->assertTrue($this->resolver->isMissing('foo'));
    }
    public function testIsNotMissingIfSet()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->assertFalse($this->resolver->isMissing('foo'));
        $this->resolver->setRequired('foo');
        $this->assertFalse($this->resolver->isMissing('foo'));
    }
    public function testIsNotMissingAfterRemove()
    {
        $this->resolver->setRequired('foo');
        $this->resolver->remove('foo');
        $this->assertFalse($this->resolver->isMissing('foo'));
    }
    public function testIsNotMissingAfterClear()
    {
        $this->resolver->setRequired('foo');
        $this->resolver->clear();
        $this->assertFalse($this->resolver->isRequired('foo'));
    }
    public function testGetMissingOptions()
    {
        $this->resolver->setRequired(['foo', 'bar']);
        $this->resolver->setDefault('bam', 'baz');
        $this->resolver->setDefault('foo', 'boo');
        $this->assertSame(['bar'], $this->resolver->getMissingOptions());
    }
    public function testFailIfSetDefinedFromLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->setDefined('bar');
        });
        $this->resolver->resolve();
    }
    public function testDefinedOptionsNotIncludedInResolvedOptions()
    {
        $this->resolver->setDefined('foo');
        $this->assertSame([], $this->resolver->resolve());
    }
    public function testDefinedOptionsIncludedIfDefaultSetBefore()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setDefined('foo');
        $this->assertSame(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testDefinedOptionsIncludedIfDefaultSetAfter()
    {
        $this->resolver->setDefined('foo');
        $this->resolver->setDefault('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testDefinedOptionsIncludedIfPassedToResolve()
    {
        $this->resolver->setDefined('foo');
        $this->assertSame(['foo' => 'bar'], $this->resolver->resolve(['foo' => 'bar']));
    }
    public function testIsDefined()
    {
        $this->assertFalse($this->resolver->isDefined('foo'));
        $this->resolver->setDefined('foo');
        $this->assertTrue($this->resolver->isDefined('foo'));
    }
    public function testLazyOptionsAreDefined()
    {
        $this->assertFalse($this->resolver->isDefined('foo'));
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
        });
        $this->assertTrue($this->resolver->isDefined('foo'));
    }
    public function testRequiredOptionsAreDefined()
    {
        $this->assertFalse($this->resolver->isDefined('foo'));
        $this->resolver->setRequired('foo');
        $this->assertTrue($this->resolver->isDefined('foo'));
    }
    public function testSetOptionsAreDefined()
    {
        $this->assertFalse($this->resolver->isDefined('foo'));
        $this->resolver->setDefault('foo', 'bar');
        $this->assertTrue($this->resolver->isDefined('foo'));
    }
    public function testGetDefinedOptions()
    {
        $this->resolver->setDefined(['foo', 'bar']);
        $this->resolver->setDefault('baz', 'bam');
        $this->resolver->setRequired('boo');
        $this->assertSame(['foo', 'bar', 'baz', 'boo'], $this->resolver->getDefinedOptions());
    }
    public function testRemovedOptionsAreNotDefined()
    {
        $this->assertFalse($this->resolver->isDefined('foo'));
        $this->resolver->setDefined('foo');
        $this->assertTrue($this->resolver->isDefined('foo'));
        $this->resolver->remove('foo');
        $this->assertFalse($this->resolver->isDefined('foo'));
    }
    public function testClearedOptionsAreNotDefined()
    {
        $this->assertFalse($this->resolver->isDefined('foo'));
        $this->resolver->setDefined('foo');
        $this->assertTrue($this->resolver->isDefined('foo'));
        $this->resolver->clear();
        $this->assertFalse($this->resolver->isDefined('foo'));
    }
    public function testSetAllowedTypesFailsIfUnknownOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\UndefinedOptionsException');
        $this->resolver->setAllowedTypes('foo', 'string');
    }
    public function testResolveTypedArray()
    {
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'string[]');
        $options = $this->resolver->resolve(['foo' => ['bar', 'baz']]);
        $this->assertSame(['foo' => ['bar', 'baz']], $options);
    }
    public function testFailIfSetAllowedTypesFromLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->setAllowedTypes('bar', 'string');
        });
        $this->resolver->setDefault('bar', 'baz');
        $this->resolver->resolve();
    }
    public function testResolveFailsIfInvalidTypedArray()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value array is expected to be of type "int[]", but one of the elements is of type "DateTime".');
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'int[]');
        $this->resolver->resolve(['foo' => [new \DateTime()]]);
    }
    public function testResolveFailsWithNonArray()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value "bar" is expected to be of type "int[]", but is of type "string".');
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'int[]');
        $this->resolver->resolve(['foo' => 'bar']);
    }
    public function testResolveFailsIfTypedArrayContainsInvalidTypes()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value array is expected to be of type "int[]", but one of the elements is of type "stdClass|array|DateTime".');
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'int[]');
        $values = \range(1, 5);
        $values[] = new \stdClass();
        $values[] = [];
        $values[] = new \DateTime();
        $values[] = 123;
        $this->resolver->resolve(['foo' => $values]);
    }
    public function testResolveFailsWithCorrectLevelsButWrongScalar()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value array is expected to be of type "int[][]", but one of the elements is of type "double".');
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'int[][]');
        $this->resolver->resolve(['foo' => [[1.2]]]);
    }
    /**
     * @dataProvider provideInvalidTypes
     */
    public function testResolveFailsIfInvalidType($actualType, $allowedType, $exceptionMessage)
    {
        $this->resolver->setDefined('option');
        $this->resolver->setAllowedTypes('option', $allowedType);
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage($exceptionMessage);
        $this->resolver->resolve(['option' => $actualType]);
    }
    public function provideInvalidTypes()
    {
        return [[\true, 'string', 'The option "option" with value true is expected to be of type "string", but is of type "boolean".'], [\false, 'string', 'The option "option" with value false is expected to be of type "string", but is of type "boolean".'], [\fopen(__FILE__, 'r'), 'string', 'The option "option" with value resource is expected to be of type "string", but is of type "resource".'], [[], 'string', 'The option "option" with value array is expected to be of type "string", but is of type "array".'], [new \MolliePrefix\Symfony\Component\OptionsResolver\OptionsResolver(), 'string', 'The option "option" with value Symfony\\Component\\OptionsResolver\\OptionsResolver is expected to be of type "string", but is of type "Symfony\\Component\\OptionsResolver\\OptionsResolver".'], [42, 'string', 'The option "option" with value 42 is expected to be of type "string", but is of type "integer".'], [null, 'string', 'The option "option" with value null is expected to be of type "string", but is of type "NULL".'], ['bar', '\\stdClass', 'The option "option" with value "bar" is expected to be of type "\\stdClass", but is of type "string".'], [['foo', 12], 'string[]', 'The option "option" with value array is expected to be of type "string[]", but one of the elements is of type "integer".'], [123, ['string[]', 'string'], 'The option "option" with value 123 is expected to be of type "string[]" or "string", but is of type "integer".'], [[null], ['string[]', 'string'], 'The option "option" with value array is expected to be of type "string[]" or "string", but one of the elements is of type "NULL".'], [['string', null], ['string[]', 'string'], 'The option "option" with value array is expected to be of type "string[]" or "string", but one of the elements is of type "NULL".'], [[\stdClass::class], ['string'], 'The option "option" with value array is expected to be of type "string", but is of type "array".']];
    }
    public function testResolveSucceedsIfValidType()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedTypes('foo', 'string');
        $this->assertNotEmpty($this->resolver->resolve());
    }
    public function testResolveFailsIfInvalidTypeMultiple()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value 42 is expected to be of type "string" or "bool", but is of type "integer".');
        $this->resolver->setDefault('foo', 42);
        $this->resolver->setAllowedTypes('foo', ['string', 'bool']);
        $this->resolver->resolve();
    }
    public function testResolveSucceedsIfValidTypeMultiple()
    {
        $this->resolver->setDefault('foo', \true);
        $this->resolver->setAllowedTypes('foo', ['string', 'bool']);
        $this->assertNotEmpty($this->resolver->resolve());
    }
    public function testResolveSucceedsIfInstanceOfClass()
    {
        $this->resolver->setDefault('foo', new \stdClass());
        $this->resolver->setAllowedTypes('foo', '\\stdClass');
        $this->assertNotEmpty($this->resolver->resolve());
    }
    public function testResolveSucceedsIfTypedArray()
    {
        $this->resolver->setDefault('foo', null);
        $this->resolver->setAllowedTypes('foo', ['null', 'DateTime[]']);
        $data = ['foo' => [new \DateTime(), new \DateTime()]];
        $result = $this->resolver->resolve($data);
        $this->assertEquals($data, $result);
    }
    public function testResolveFailsIfNotInstanceOfClass()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedTypes('foo', '\\stdClass');
        $this->resolver->resolve();
    }
    public function testAddAllowedTypesFailsIfUnknownOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\UndefinedOptionsException');
        $this->resolver->addAllowedTypes('foo', 'string');
    }
    public function testFailIfAddAllowedTypesFromLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->addAllowedTypes('bar', 'string');
        });
        $this->resolver->setDefault('bar', 'baz');
        $this->resolver->resolve();
    }
    public function testResolveFailsIfInvalidAddedType()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->resolver->setDefault('foo', 42);
        $this->resolver->addAllowedTypes('foo', 'string');
        $this->resolver->resolve();
    }
    public function testResolveSucceedsIfValidAddedType()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->addAllowedTypes('foo', 'string');
        $this->assertNotEmpty($this->resolver->resolve());
    }
    public function testResolveFailsIfInvalidAddedTypeMultiple()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->resolver->setDefault('foo', 42);
        $this->resolver->addAllowedTypes('foo', ['string', 'bool']);
        $this->resolver->resolve();
    }
    public function testResolveSucceedsIfValidAddedTypeMultiple()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->addAllowedTypes('foo', ['string', 'bool']);
        $this->assertNotEmpty($this->resolver->resolve());
    }
    public function testAddAllowedTypesDoesNotOverwrite()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedTypes('foo', 'string');
        $this->resolver->addAllowedTypes('foo', 'bool');
        $this->resolver->setDefault('foo', 'bar');
        $this->assertNotEmpty($this->resolver->resolve());
    }
    public function testAddAllowedTypesDoesNotOverwrite2()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedTypes('foo', 'string');
        $this->resolver->addAllowedTypes('foo', 'bool');
        $this->resolver->setDefault('foo', \false);
        $this->assertNotEmpty($this->resolver->resolve());
    }
    public function testSetAllowedValuesFailsIfUnknownOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\UndefinedOptionsException');
        $this->resolver->setAllowedValues('foo', 'bar');
    }
    public function testFailIfSetAllowedValuesFromLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->setAllowedValues('bar', 'baz');
        });
        $this->resolver->setDefault('bar', 'baz');
        $this->resolver->resolve();
    }
    public function testResolveFailsIfInvalidValue()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value 42 is invalid. Accepted values are: "bar".');
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedValues('foo', 'bar');
        $this->resolver->resolve(['foo' => 42]);
    }
    public function testResolveFailsIfInvalidValueIsNull()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value null is invalid. Accepted values are: "bar".');
        $this->resolver->setDefault('foo', null);
        $this->resolver->setAllowedValues('foo', 'bar');
        $this->resolver->resolve();
    }
    public function testResolveFailsIfInvalidValueStrict()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->resolver->setDefault('foo', 42);
        $this->resolver->setAllowedValues('foo', '42');
        $this->resolver->resolve();
    }
    public function testResolveSucceedsIfValidValue()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedValues('foo', 'bar');
        $this->assertEquals(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testResolveSucceedsIfValidValueIsNull()
    {
        $this->resolver->setDefault('foo', null);
        $this->resolver->setAllowedValues('foo', null);
        $this->assertEquals(['foo' => null], $this->resolver->resolve());
    }
    public function testResolveFailsIfInvalidValueMultiple()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value 42 is invalid. Accepted values are: "bar", false, null.');
        $this->resolver->setDefault('foo', 42);
        $this->resolver->setAllowedValues('foo', ['bar', \false, null]);
        $this->resolver->resolve();
    }
    public function testResolveSucceedsIfValidValueMultiple()
    {
        $this->resolver->setDefault('foo', 'baz');
        $this->resolver->setAllowedValues('foo', ['bar', 'baz']);
        $this->assertEquals(['foo' => 'baz'], $this->resolver->resolve());
    }
    public function testResolveFailsIfClosureReturnsFalse()
    {
        $this->resolver->setDefault('foo', 42);
        $this->resolver->setAllowedValues('foo', function ($value) use(&$passedValue) {
            $passedValue = $value;
            return \false;
        });
        try {
            $this->resolver->resolve();
            $this->fail('Should fail');
        } catch (\MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException $e) {
        }
        $this->assertSame(42, $passedValue);
    }
    public function testResolveSucceedsIfClosureReturnsTrue()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedValues('foo', function ($value) use(&$passedValue) {
            $passedValue = $value;
            return \true;
        });
        $this->assertEquals(['foo' => 'bar'], $this->resolver->resolve());
        $this->assertSame('bar', $passedValue);
    }
    public function testResolveFailsIfAllClosuresReturnFalse()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->resolver->setDefault('foo', 42);
        $this->resolver->setAllowedValues('foo', [function () {
            return \false;
        }, function () {
            return \false;
        }, function () {
            return \false;
        }]);
        $this->resolver->resolve();
    }
    public function testResolveSucceedsIfAnyClosureReturnsTrue()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedValues('foo', [function () {
            return \false;
        }, function () {
            return \true;
        }, function () {
            return \false;
        }]);
        $this->assertEquals(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testAddAllowedValuesFailsIfUnknownOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\UndefinedOptionsException');
        $this->resolver->addAllowedValues('foo', 'bar');
    }
    public function testFailIfAddAllowedValuesFromLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->addAllowedValues('bar', 'baz');
        });
        $this->resolver->setDefault('bar', 'baz');
        $this->resolver->resolve();
    }
    public function testResolveFailsIfInvalidAddedValue()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->resolver->setDefault('foo', 42);
        $this->resolver->addAllowedValues('foo', 'bar');
        $this->resolver->resolve();
    }
    public function testResolveSucceedsIfValidAddedValue()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->addAllowedValues('foo', 'bar');
        $this->assertEquals(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testResolveSucceedsIfValidAddedValueIsNull()
    {
        $this->resolver->setDefault('foo', null);
        $this->resolver->addAllowedValues('foo', null);
        $this->assertEquals(['foo' => null], $this->resolver->resolve());
    }
    public function testResolveFailsIfInvalidAddedValueMultiple()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->resolver->setDefault('foo', 42);
        $this->resolver->addAllowedValues('foo', ['bar', 'baz']);
        $this->resolver->resolve();
    }
    public function testResolveSucceedsIfValidAddedValueMultiple()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->addAllowedValues('foo', ['bar', 'baz']);
        $this->assertEquals(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testAddAllowedValuesDoesNotOverwrite()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedValues('foo', 'bar');
        $this->resolver->addAllowedValues('foo', 'baz');
        $this->assertEquals(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testAddAllowedValuesDoesNotOverwrite2()
    {
        $this->resolver->setDefault('foo', 'baz');
        $this->resolver->setAllowedValues('foo', 'bar');
        $this->resolver->addAllowedValues('foo', 'baz');
        $this->assertEquals(['foo' => 'baz'], $this->resolver->resolve());
    }
    public function testResolveFailsIfAllAddedClosuresReturnFalse()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->resolver->setDefault('foo', 42);
        $this->resolver->setAllowedValues('foo', function () {
            return \false;
        });
        $this->resolver->addAllowedValues('foo', function () {
            return \false;
        });
        $this->resolver->resolve();
    }
    public function testResolveSucceedsIfAnyAddedClosureReturnsTrue()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedValues('foo', function () {
            return \false;
        });
        $this->resolver->addAllowedValues('foo', function () {
            return \true;
        });
        $this->assertEquals(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testResolveSucceedsIfAnyAddedClosureReturnsTrue2()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedValues('foo', function () {
            return \true;
        });
        $this->resolver->addAllowedValues('foo', function () {
            return \false;
        });
        $this->assertEquals(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testSetNormalizerReturnsThis()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->assertSame($this->resolver, $this->resolver->setNormalizer('foo', function () {
        }));
    }
    public function testSetNormalizerClosure()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setNormalizer('foo', function () {
            return 'normalized';
        });
        $this->assertEquals(['foo' => 'normalized'], $this->resolver->resolve());
    }
    public function testSetNormalizerFailsIfUnknownOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\UndefinedOptionsException');
        $this->resolver->setNormalizer('foo', function () {
        });
    }
    public function testFailIfSetNormalizerFromLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->setNormalizer('foo', function () {
            });
        });
        $this->resolver->setDefault('bar', 'baz');
        $this->resolver->resolve();
    }
    public function testNormalizerReceivesSetOption()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setNormalizer('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options, $value) {
            return 'normalized[' . $value . ']';
        });
        $this->assertEquals(['foo' => 'normalized[bar]'], $this->resolver->resolve());
    }
    public function testNormalizerReceivesPassedOption()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setNormalizer('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options, $value) {
            return 'normalized[' . $value . ']';
        });
        $resolved = $this->resolver->resolve(['foo' => 'baz']);
        $this->assertEquals(['foo' => 'normalized[baz]'], $resolved);
    }
    public function testValidateTypeBeforeNormalization()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedTypes('foo', 'int');
        $this->resolver->setNormalizer('foo', function () {
            \MolliePrefix\PHPUnit\Framework\Assert::fail('Should not be called.');
        });
        $this->resolver->resolve();
    }
    public function testValidateValueBeforeNormalization()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedValues('foo', 'baz');
        $this->resolver->setNormalizer('foo', function () {
            \MolliePrefix\PHPUnit\Framework\Assert::fail('Should not be called.');
        });
        $this->resolver->resolve();
    }
    public function testNormalizerCanAccessOtherOptions()
    {
        $this->resolver->setDefault('default', 'bar');
        $this->resolver->setDefault('norm', 'baz');
        $this->resolver->setNormalizer('norm', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            /* @var TestCase $test */
            \MolliePrefix\PHPUnit\Framework\Assert::assertSame('bar', $options['default']);
            return 'normalized';
        });
        $this->assertEquals(['default' => 'bar', 'norm' => 'normalized'], $this->resolver->resolve());
    }
    public function testNormalizerCanAccessLazyOptions()
    {
        $this->resolver->setDefault('lazy', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            return 'bar';
        });
        $this->resolver->setDefault('norm', 'baz');
        $this->resolver->setNormalizer('norm', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            /* @var TestCase $test */
            \MolliePrefix\PHPUnit\Framework\Assert::assertEquals('bar', $options['lazy']);
            return 'normalized';
        });
        $this->assertEquals(['lazy' => 'bar', 'norm' => 'normalized'], $this->resolver->resolve());
    }
    public function testFailIfCyclicDependencyBetweenNormalizers()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\OptionDefinitionException');
        $this->resolver->setDefault('norm1', 'bar');
        $this->resolver->setDefault('norm2', 'baz');
        $this->resolver->setNormalizer('norm1', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options['norm2'];
        });
        $this->resolver->setNormalizer('norm2', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options['norm1'];
        });
        $this->resolver->resolve();
    }
    public function testFailIfCyclicDependencyBetweenNormalizerAndLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\OptionDefinitionException');
        $this->resolver->setDefault('lazy', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options['norm'];
        });
        $this->resolver->setDefault('norm', 'baz');
        $this->resolver->setNormalizer('norm', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options['lazy'];
        });
        $this->resolver->resolve();
    }
    public function testCaughtExceptionFromNormalizerDoesNotCrashOptionResolver()
    {
        $throw = \true;
        $this->resolver->setDefaults(['catcher' => null, 'thrower' => null]);
        $this->resolver->setNormalizer('catcher', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            try {
                return $options['thrower'];
            } catch (\Exception $e) {
                return \false;
            }
        });
        $this->resolver->setNormalizer('thrower', function () use(&$throw) {
            if ($throw) {
                $throw = \false;
                throw new \UnexpectedValueException('throwing');
            }
            return \true;
        });
        $this->assertSame(['catcher' => \false, 'thrower' => \true], $this->resolver->resolve());
    }
    public function testCaughtExceptionFromLazyDoesNotCrashOptionResolver()
    {
        $throw = \true;
        $this->resolver->setDefault('catcher', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            try {
                return $options['thrower'];
            } catch (\Exception $e) {
                return \false;
            }
        });
        $this->resolver->setDefault('thrower', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) use(&$throw) {
            if ($throw) {
                $throw = \false;
                throw new \UnexpectedValueException('throwing');
            }
            return \true;
        });
        $this->assertSame(['catcher' => \false, 'thrower' => \true], $this->resolver->resolve());
    }
    public function testInvokeEachNormalizerOnlyOnce()
    {
        $calls = 0;
        $this->resolver->setDefault('norm1', 'bar');
        $this->resolver->setDefault('norm2', 'baz');
        $this->resolver->setNormalizer('norm1', function ($options) use(&$calls) {
            \MolliePrefix\PHPUnit\Framework\Assert::assertSame(1, ++$calls);
            $options['norm2'];
        });
        $this->resolver->setNormalizer('norm2', function () use(&$calls) {
            \MolliePrefix\PHPUnit\Framework\Assert::assertSame(2, ++$calls);
        });
        $this->resolver->resolve();
        $this->assertSame(2, $calls);
    }
    public function testNormalizerNotCalledForUnsetOptions()
    {
        $this->resolver->setDefined('norm');
        $this->resolver->setNormalizer('norm', function () {
            \MolliePrefix\PHPUnit\Framework\Assert::fail('Should not be called.');
        });
        $this->assertEmpty($this->resolver->resolve());
    }
    public function testSetDefaultsReturnsThis()
    {
        $this->assertSame($this->resolver, $this->resolver->setDefaults(['foo', 'bar']));
    }
    public function testSetDefaults()
    {
        $this->resolver->setDefault('one', '1');
        $this->resolver->setDefault('two', 'bar');
        $this->resolver->setDefaults(['two' => '2', 'three' => '3']);
        $this->assertEquals(['one' => '1', 'two' => '2', 'three' => '3'], $this->resolver->resolve());
    }
    public function testFailIfSetDefaultsFromLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->setDefaults(['two' => '2']);
        });
        $this->resolver->resolve();
    }
    public function testRemoveReturnsThis()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->assertSame($this->resolver, $this->resolver->remove('foo'));
    }
    public function testRemoveSingleOption()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setDefault('baz', 'boo');
        $this->resolver->remove('foo');
        $this->assertSame(['baz' => 'boo'], $this->resolver->resolve());
    }
    public function testRemoveMultipleOptions()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setDefault('baz', 'boo');
        $this->resolver->setDefault('doo', 'dam');
        $this->resolver->remove(['foo', 'doo']);
        $this->assertSame(['baz' => 'boo'], $this->resolver->resolve());
    }
    public function testRemoveLazyOption()
    {
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            return 'lazy';
        });
        $this->resolver->remove('foo');
        $this->assertSame([], $this->resolver->resolve());
    }
    public function testRemoveNormalizer()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setNormalizer('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options, $value) {
            return 'normalized';
        });
        $this->resolver->remove('foo');
        $this->resolver->setDefault('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testRemoveAllowedTypes()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedTypes('foo', 'int');
        $this->resolver->remove('foo');
        $this->resolver->setDefault('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testRemoveAllowedValues()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedValues('foo', ['baz', 'boo']);
        $this->resolver->remove('foo');
        $this->resolver->setDefault('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testFailIfRemoveFromLazyOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->remove('bar');
        });
        $this->resolver->setDefault('bar', 'baz');
        $this->resolver->resolve();
    }
    public function testRemoveUnknownOptionIgnored()
    {
        $this->assertNotNull($this->resolver->remove('foo'));
    }
    public function testClearReturnsThis()
    {
        $this->assertSame($this->resolver, $this->resolver->clear());
    }
    public function testClearRemovesAllOptions()
    {
        $this->resolver->setDefault('one', 1);
        $this->resolver->setDefault('two', 2);
        $this->resolver->clear();
        $this->assertEmpty($this->resolver->resolve());
    }
    public function testClearLazyOption()
    {
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            return 'lazy';
        });
        $this->resolver->clear();
        $this->assertSame([], $this->resolver->resolve());
    }
    public function testClearNormalizer()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setNormalizer('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options, $value) {
            return 'normalized';
        });
        $this->resolver->clear();
        $this->resolver->setDefault('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testClearAllowedTypes()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedTypes('foo', 'int');
        $this->resolver->clear();
        $this->resolver->setDefault('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testClearAllowedValues()
    {
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setAllowedValues('foo', 'baz');
        $this->resolver->clear();
        $this->resolver->setDefault('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $this->resolver->resolve());
    }
    public function testFailIfClearFromLazyption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options->clear();
        });
        $this->resolver->setDefault('bar', 'baz');
        $this->resolver->resolve();
    }
    public function testClearOptionAndNormalizer()
    {
        $this->resolver->setDefault('foo1', 'bar');
        $this->resolver->setNormalizer('foo1', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            return '';
        });
        $this->resolver->setDefault('foo2', 'bar');
        $this->resolver->setNormalizer('foo2', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            return '';
        });
        $this->resolver->clear();
        $this->assertEmpty($this->resolver->resolve());
    }
    public function testArrayAccess()
    {
        $this->resolver->setDefault('default1', 0);
        $this->resolver->setDefault('default2', 1);
        $this->resolver->setRequired('required');
        $this->resolver->setDefined('defined');
        $this->resolver->setDefault('lazy1', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            return 'lazy';
        });
        $this->resolver->setDefault('lazy2', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            \MolliePrefix\PHPUnit\Framework\Assert::assertArrayHasKey('default1', $options);
            \MolliePrefix\PHPUnit\Framework\Assert::assertArrayHasKey('default2', $options);
            \MolliePrefix\PHPUnit\Framework\Assert::assertArrayHasKey('required', $options);
            \MolliePrefix\PHPUnit\Framework\Assert::assertArrayHasKey('lazy1', $options);
            \MolliePrefix\PHPUnit\Framework\Assert::assertArrayHasKey('lazy2', $options);
            \MolliePrefix\PHPUnit\Framework\Assert::assertArrayNotHasKey('defined', $options);
            \MolliePrefix\PHPUnit\Framework\Assert::assertSame(0, $options['default1']);
            \MolliePrefix\PHPUnit\Framework\Assert::assertSame(42, $options['default2']);
            \MolliePrefix\PHPUnit\Framework\Assert::assertSame('value', $options['required']);
            \MolliePrefix\PHPUnit\Framework\Assert::assertSame('lazy', $options['lazy1']);
            // Obviously $options['lazy'] and $options['defined'] cannot be
            // accessed
        });
        $this->resolver->resolve(['default2' => 42, 'required' => 'value']);
    }
    public function testArrayAccessGetFailsOutsideResolve()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('default', 0);
        $this->resolver['default'];
    }
    public function testArrayAccessExistsFailsOutsideResolve()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('default', 0);
        isset($this->resolver['default']);
    }
    public function testArrayAccessSetNotSupported()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver['default'] = 0;
    }
    public function testArrayAccessUnsetNotSupported()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('default', 0);
        unset($this->resolver['default']);
    }
    public function testFailIfGetNonExisting()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\NoSuchOptionException');
        $this->expectExceptionMessage('The option "undefined" does not exist. Defined options are: "foo", "lazy".');
        $this->resolver->setDefault('foo', 'bar');
        $this->resolver->setDefault('lazy', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options['undefined'];
        });
        $this->resolver->resolve();
    }
    public function testFailIfGetDefinedButUnset()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\NoSuchOptionException');
        $this->expectExceptionMessage('The optional option "defined" has no value set. You should make sure it is set with "isset" before reading it.');
        $this->resolver->setDefined('defined');
        $this->resolver->setDefault('lazy', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options['defined'];
        });
        $this->resolver->resolve();
    }
    public function testFailIfCyclicDependency()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\OptionDefinitionException');
        $this->resolver->setDefault('lazy1', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options['lazy2'];
        });
        $this->resolver->setDefault('lazy2', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            $options['lazy1'];
        });
        $this->resolver->resolve();
    }
    public function testCount()
    {
        $this->resolver->setDefault('default', 0);
        $this->resolver->setRequired('required');
        $this->resolver->setDefined('defined');
        $this->resolver->setDefault('lazy1', function () {
        });
        $this->resolver->setDefault('lazy2', function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options) {
            \MolliePrefix\PHPUnit\Framework\Assert::assertCount(4, $options);
        });
        $this->assertCount(4, $this->resolver->resolve(['required' => 'value']));
    }
    /**
     * In resolve() we count the options that are actually set (which may be
     * only a subset of the defined options). Outside of resolve(), it's not
     * clear what is counted.
     */
    public function testCountFailsOutsideResolve()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\AccessException');
        $this->resolver->setDefault('foo', 0);
        $this->resolver->setRequired('bar');
        $this->resolver->setDefined('bar');
        $this->resolver->setDefault('lazy1', function () {
        });
        \count($this->resolver);
    }
    public function testNestedArrays()
    {
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'int[][]');
        $this->assertEquals(['foo' => [[1, 2]]], $this->resolver->resolve(['foo' => [[1, 2]]]));
    }
    public function testNested2Arrays()
    {
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'int[][][][]');
        $this->assertEquals(['foo' => [[[[1, 2]]]]], $this->resolver->resolve(['foo' => [[[[1, 2]]]]]));
    }
    public function testNestedArraysException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value array is expected to be of type "float[][][][]", but one of the elements is of type "integer".');
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'float[][][][]');
        $this->resolver->resolve(['foo' => [[[[1, 2]]]]]);
    }
    public function testNestedArrayException1()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value array is expected to be of type "int[][]", but one of the elements is of type "boolean|string|array".');
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'int[][]');
        $this->resolver->resolve(['foo' => [[1, \true, 'str', [2, 3]]]]);
    }
    public function testNestedArrayException2()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value array is expected to be of type "int[][]", but one of the elements is of type "boolean|string|array".');
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'int[][]');
        $this->resolver->resolve(['foo' => [[\true, 'str', [2, 3]]]]);
    }
    public function testNestedArrayException3()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value array is expected to be of type "string[][][]", but one of the elements is of type "string|integer".');
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'string[][][]');
        $this->resolver->resolve(['foo' => [['str', [1, 2]]]]);
    }
    public function testNestedArrayException4()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value array is expected to be of type "string[][][]", but one of the elements is of type "integer".');
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'string[][][]');
        $this->resolver->resolve(['foo' => [[['str'], [1, 2]]]]);
    }
    public function testNestedArrayException5()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException');
        $this->expectExceptionMessage('The option "foo" with value array is expected to be of type "string[]", but one of the elements is of type "array".');
        $this->resolver->setDefined('foo');
        $this->resolver->setAllowedTypes('foo', 'string[]');
        $this->resolver->resolve(['foo' => [[['str'], [1, 2]]]]);
    }
}
