<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Finder\Tests\Iterator;

use MolliePrefix\Symfony\Component\Finder\Iterator\FilecontentFilterIterator;
class FilecontentFilterIteratorTest extends \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\IteratorTestCase
{
    public function testAccept()
    {
        $inner = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\MockFileListIterator(['test.txt']);
        $iterator = new \MolliePrefix\Symfony\Component\Finder\Iterator\FilecontentFilterIterator($inner, [], []);
        $this->assertIterator(['test.txt'], $iterator);
    }
    public function testDirectory()
    {
        $inner = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\MockFileListIterator(['directory']);
        $iterator = new \MolliePrefix\Symfony\Component\Finder\Iterator\FilecontentFilterIterator($inner, ['directory'], []);
        $this->assertIterator([], $iterator);
    }
    public function testUnreadableFile()
    {
        $inner = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\MockFileListIterator(['file r-']);
        $iterator = new \MolliePrefix\Symfony\Component\Finder\Iterator\FilecontentFilterIterator($inner, ['file r-'], []);
        $this->assertIterator([], $iterator);
    }
    /**
     * @dataProvider getTestFilterData
     */
    public function testFilter(\Iterator $inner, array $matchPatterns, array $noMatchPatterns, array $resultArray)
    {
        $iterator = new \MolliePrefix\Symfony\Component\Finder\Iterator\FilecontentFilterIterator($inner, $matchPatterns, $noMatchPatterns);
        $this->assertIterator($resultArray, $iterator);
    }
    public function getTestFilterData()
    {
        $inner = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\MockFileListIterator();
        $inner[] = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\MockSplFileInfo(['name' => 'a.txt', 'contents' => 'Lorem ipsum...', 'type' => 'file', 'mode' => 'r+']);
        $inner[] = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\MockSplFileInfo(['name' => 'b.yml', 'contents' => 'dolor sit...', 'type' => 'file', 'mode' => 'r+']);
        $inner[] = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\MockSplFileInfo(['name' => 'some/other/dir/third.php', 'contents' => 'amet...', 'type' => 'file', 'mode' => 'r+']);
        $inner[] = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\MockSplFileInfo(['name' => 'unreadable-file.txt', 'contents' => \false, 'type' => 'file', 'mode' => 'r+']);
        return [[$inner, ['.'], [], ['a.txt', 'b.yml', 'some/other/dir/third.php']], [$inner, ['ipsum'], [], ['a.txt']], [$inner, ['i', 'amet'], ['Lorem', 'amet'], ['b.yml']]];
    }
}
