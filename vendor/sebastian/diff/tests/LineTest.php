<?php

/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\SebastianBergmann\Diff;

use MolliePrefix\PHPUnit\Framework\TestCase;
/**
 * @covers SebastianBergmann\Diff\Line
 */
class LineTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @var Line
     */
    private $line;
    protected function setUp()
    {
        $this->line = new \MolliePrefix\SebastianBergmann\Diff\Line();
    }
    public function testCanBeCreatedWithoutArguments()
    {
        $this->assertInstanceOf('MolliePrefix\\SebastianBergmann\\Diff\\Line', $this->line);
    }
    public function testTypeCanBeRetrieved()
    {
        $this->assertEquals(\MolliePrefix\SebastianBergmann\Diff\Line::UNCHANGED, $this->line->getType());
    }
    public function testContentCanBeRetrieved()
    {
        $this->assertEquals('', $this->line->getContent());
    }
}
