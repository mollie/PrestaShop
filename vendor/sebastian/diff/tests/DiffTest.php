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
 * @covers SebastianBergmann\Diff\Diff
 *
 * @uses SebastianBergmann\Diff\Chunk
 */
final class DiffTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testGettersAfterConstructionWithDefault()
    {
        $from = 'line1a';
        $to = 'line2a';
        $diff = new \MolliePrefix\SebastianBergmann\Diff\Diff($from, $to);
        $this->assertSame($from, $diff->getFrom());
        $this->assertSame($to, $diff->getTo());
        $this->assertSame(array(), $diff->getChunks(), 'Expect chunks to be default value "array()".');
    }
    public function testGettersAfterConstructionWithChunks()
    {
        $from = 'line1b';
        $to = 'line2b';
        $chunks = array(new \MolliePrefix\SebastianBergmann\Diff\Chunk(), new \MolliePrefix\SebastianBergmann\Diff\Chunk(2, 3));
        $diff = new \MolliePrefix\SebastianBergmann\Diff\Diff($from, $to, $chunks);
        $this->assertSame($from, $diff->getFrom());
        $this->assertSame($to, $diff->getTo());
        $this->assertSame($chunks, $diff->getChunks(), 'Expect chunks to be passed value.');
    }
    public function testSetChunksAfterConstruction()
    {
        $diff = new \MolliePrefix\SebastianBergmann\Diff\Diff('line1c', 'line2c');
        $this->assertSame(array(), $diff->getChunks(), 'Expect chunks to be default value "array()".');
        $chunks = array(new \MolliePrefix\SebastianBergmann\Diff\Chunk(), new \MolliePrefix\SebastianBergmann\Diff\Chunk(2, 3));
        $diff->setChunks($chunks);
        $this->assertSame($chunks, $diff->getChunks(), 'Expect chunks to be passed value.');
    }
}
