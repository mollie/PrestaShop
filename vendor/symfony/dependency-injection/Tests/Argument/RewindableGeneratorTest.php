<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Argument;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
class RewindableGeneratorTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testImplementsCountable()
    {
        $this->assertInstanceOf(\Countable::class, new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\RewindableGenerator(function () {
            (yield 1);
        }, 1));
    }
    public function testCountUsesProvidedValue()
    {
        $generator = new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\RewindableGenerator(function () {
            (yield 1);
        }, 3);
        $this->assertCount(3, $generator);
    }
    public function testCountUsesProvidedValueAsCallback()
    {
        $called = 0;
        $generator = new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\RewindableGenerator(function () {
            (yield 1);
        }, function () use(&$called) {
            ++$called;
            return 3;
        });
        $this->assertSame(0, $called, 'Count callback is called lazily');
        $this->assertCount(3, $generator);
        \count($generator);
        $this->assertSame(1, $called, 'Count callback is called only once');
    }
}
