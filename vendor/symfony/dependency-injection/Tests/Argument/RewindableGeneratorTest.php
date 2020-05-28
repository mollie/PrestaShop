<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Argument;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
class RewindableGeneratorTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testImplementsCountable()
    {
        $this->assertInstanceOf(\Countable::class, new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\RewindableGenerator(function () {
            (yield 1);
        }, 1));
    }
    public function testCountUsesProvidedValue()
    {
        $generator = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\RewindableGenerator(function () {
            (yield 1);
        }, 3);
        $this->assertCount(3, $generator);
    }
    public function testCountUsesProvidedValueAsCallback()
    {
        $called = 0;
        $generator = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\RewindableGenerator(function () {
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
