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

use MolliePrefix\Symfony\Component\Finder\Iterator\FileTypeFilterIterator;
class FileTypeFilterIteratorTest extends \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\RealIteratorTestCase
{
    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($mode, $expected)
    {
        $inner = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\InnerTypeIterator(self::$files);
        $iterator = new \MolliePrefix\Symfony\Component\Finder\Iterator\FileTypeFilterIterator($inner, $mode);
        $this->assertIterator($expected, $iterator);
    }
    public function getAcceptData()
    {
        $onlyFiles = ['test.py', 'foo/bar.tmp', 'test.php', '.bar', '.foo/.bar', '.foo/bar', 'foo bar'];
        $onlyDirectories = ['.git', 'foo', 'toto', 'toto/.git', '.foo'];
        return [[\MolliePrefix\Symfony\Component\Finder\Iterator\FileTypeFilterIterator::ONLY_FILES, $this->toAbsolute($onlyFiles)], [\MolliePrefix\Symfony\Component\Finder\Iterator\FileTypeFilterIterator::ONLY_DIRECTORIES, $this->toAbsolute($onlyDirectories)]];
    }
}
class InnerTypeIterator extends \ArrayIterator
{
    public function current()
    {
        return new \SplFileInfo(parent::current());
    }
    public function isFile()
    {
        return $this->current()->isFile();
    }
    public function isDir()
    {
        return $this->current()->isDir();
    }
}
