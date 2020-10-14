<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Stopwatch\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Stopwatch\Stopwatch;
/**
 * StopwatchTest.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @group time-sensitive
 */
class StopwatchTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    const DELTA = 20;
    public function testStart()
    {
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $event = $stopwatch->start('foo', 'cat');
        $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\Stopwatch\\StopwatchEvent', $event);
        $this->assertEquals('cat', $event->getCategory());
        $this->assertSame($event, $stopwatch->getEvent('foo'));
    }
    public function testStartWithoutCategory()
    {
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $stopwatchEvent = $stopwatch->start('bar');
        $this->assertSame('default', $stopwatchEvent->getCategory());
        $this->assertSame($stopwatchEvent, $stopwatch->getEvent('bar'));
    }
    public function testIsStarted()
    {
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $stopwatch->start('foo', 'cat');
        $this->assertTrue($stopwatch->isStarted('foo'));
    }
    public function testIsNotStarted()
    {
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $this->assertFalse($stopwatch->isStarted('foo'));
    }
    public function testIsNotStartedEvent()
    {
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $sections = new \ReflectionProperty('MolliePrefix\\Symfony\\Component\\Stopwatch\\Stopwatch', 'sections');
        $sections->setAccessible(\true);
        $section = $sections->getValue($stopwatch);
        $events = new \ReflectionProperty('MolliePrefix\\Symfony\\Component\\Stopwatch\\Section', 'events');
        $events->setAccessible(\true);
        $stopwatchMockEvent = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\Stopwatch\\StopwatchEvent')->setConstructorArgs([\microtime(\true) * 1000])->getMock();
        $events->setValue(\end($section), ['foo' => $stopwatchMockEvent]);
        $this->assertFalse($stopwatch->isStarted('foo'));
    }
    public function testStop()
    {
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $stopwatch->start('foo', 'cat');
        \usleep(200000);
        $event = $stopwatch->stop('foo');
        $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\Stopwatch\\StopwatchEvent', $event);
        $this->assertEqualsWithDelta(200, $event->getDuration(), self::DELTA);
    }
    public function testUnknownEvent()
    {
        $this->expectException('LogicException');
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $stopwatch->getEvent('foo');
    }
    public function testStopWithoutStart()
    {
        $this->expectException('LogicException');
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $stopwatch->stop('foo');
    }
    public function testMorePrecision()
    {
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch(\true);
        $stopwatch->start('foo');
        $event = $stopwatch->stop('foo');
        $this->assertIsFloat($event->getStartTime());
        $this->assertIsFloat($event->getEndTime());
        $this->assertIsFloat($event->getDuration());
    }
    public function testSection()
    {
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $stopwatch->openSection();
        $stopwatch->start('foo', 'cat');
        $stopwatch->stop('foo');
        $stopwatch->start('bar', 'cat');
        $stopwatch->stop('bar');
        $stopwatch->stopSection('1');
        $stopwatch->openSection();
        $stopwatch->start('foobar', 'cat');
        $stopwatch->stop('foobar');
        $stopwatch->stopSection('2');
        $stopwatch->openSection();
        $stopwatch->start('foobar', 'cat');
        $stopwatch->stop('foobar');
        $stopwatch->stopSection('0');
        // the section is an event by itself
        $this->assertCount(3, $stopwatch->getSectionEvents('1'));
        $this->assertCount(2, $stopwatch->getSectionEvents('2'));
        $this->assertCount(2, $stopwatch->getSectionEvents('0'));
    }
    public function testReopenASection()
    {
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $stopwatch->openSection();
        $stopwatch->start('foo', 'cat');
        $stopwatch->stopSection('section');
        $stopwatch->openSection('section');
        $stopwatch->start('bar', 'cat');
        $stopwatch->stopSection('section');
        $events = $stopwatch->getSectionEvents('section');
        $this->assertCount(3, $events);
        $this->assertCount(2, $events['__section__']->getPeriods());
    }
    public function testReopenANewSectionShouldThrowAnException()
    {
        $this->expectException('LogicException');
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $stopwatch->openSection('section');
    }
    public function testReset()
    {
        $stopwatch = new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch();
        $stopwatch->openSection();
        $stopwatch->start('foo', 'cat');
        $stopwatch->reset();
        $this->assertEquals(new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch(), $stopwatch);
    }
}
