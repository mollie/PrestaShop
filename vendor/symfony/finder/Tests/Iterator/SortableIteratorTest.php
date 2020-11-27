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

use MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator;
class SortableIteratorTest extends \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\RealIteratorTestCase
{
    public function testConstructor()
    {
        try {
            new \MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator(new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\Iterator([]), 'foobar');
            $this->fail('__construct() throws an \\InvalidArgumentException exception if the mode is not valid');
        } catch (\Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '__construct() throws an \\InvalidArgumentException exception if the mode is not valid');
        }
    }
    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($mode, $expected)
    {
        if (!\is_callable($mode)) {
            switch ($mode) {
                case \MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_ACCESSED_TIME:
                    \touch(self::toAbsolute('.git'));
                    \sleep(1);
                    \file_get_contents(self::toAbsolute('.bar'));
                    break;
                case \MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_CHANGED_TIME:
                    \file_put_contents(self::toAbsolute('test.php'), 'foo');
                    \sleep(1);
                    \file_put_contents(self::toAbsolute('test.py'), 'foo');
                    break;
                case \MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_MODIFIED_TIME:
                    \file_put_contents(self::toAbsolute('test.php'), 'foo');
                    \sleep(1);
                    \file_put_contents(self::toAbsolute('test.py'), 'foo');
                    break;
            }
        }
        $inner = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\Iterator(self::$files);
        $iterator = new \MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator($inner, $mode);
        if (\MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_ACCESSED_TIME === $mode || \MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_CHANGED_TIME === $mode || \MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_MODIFIED_TIME === $mode) {
            if ('\\' === \DIRECTORY_SEPARATOR && \MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_MODIFIED_TIME !== $mode) {
                $this->markTestSkipped('Sorting by atime or ctime is not supported on Windows');
            }
            $this->assertOrderedIteratorForGroups($expected, $iterator);
        } else {
            $this->assertOrderedIterator($expected, $iterator);
        }
    }
    public function getAcceptData()
    {
        $sortByName = ['.bar', '.foo', '.foo/.bar', '.foo/bar', '.git', 'foo', 'foo bar', 'foo/bar.tmp', 'test.php', 'test.py', 'toto', 'toto/.git'];
        $sortByType = ['.foo', '.git', 'foo', 'toto', 'toto/.git', '.bar', '.foo/.bar', '.foo/bar', 'foo bar', 'foo/bar.tmp', 'test.php', 'test.py'];
        $customComparison = ['.bar', '.foo', '.foo/.bar', '.foo/bar', '.git', 'foo', 'foo bar', 'foo/bar.tmp', 'test.php', 'test.py', 'toto', 'toto/.git'];
        $sortByAccessedTime = [
            // For these two files the access time was set to 2005-10-15
            ['foo/bar.tmp', 'test.php'],
            // These files were created more or less at the same time
            ['.git', '.foo', '.foo/.bar', '.foo/bar', 'test.py', 'foo', 'toto', 'toto/.git', 'foo bar'],
            // This file was accessed after sleeping for 1 sec
            ['.bar'],
        ];
        $sortByChangedTime = [['.git', '.foo', '.foo/.bar', '.foo/bar', '.bar', 'foo', 'foo/bar.tmp', 'toto', 'toto/.git', 'foo bar'], ['test.php'], ['test.py']];
        $sortByModifiedTime = [['.git', '.foo', '.foo/.bar', '.foo/bar', '.bar', 'foo', 'foo/bar.tmp', 'toto', 'toto/.git', 'foo bar'], ['test.php'], ['test.py']];
        return [[\MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_NAME, $this->toAbsolute($sortByName)], [\MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_TYPE, $this->toAbsolute($sortByType)], [\MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_ACCESSED_TIME, $this->toAbsolute($sortByAccessedTime)], [\MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_CHANGED_TIME, $this->toAbsolute($sortByChangedTime)], [\MolliePrefix\Symfony\Component\Finder\Iterator\SortableIterator::SORT_BY_MODIFIED_TIME, $this->toAbsolute($sortByModifiedTime)], [function (\SplFileInfo $a, \SplFileInfo $b) {
            return \strcmp($a->getRealPath(), $b->getRealPath());
        }, $this->toAbsolute($customComparison)]];
    }
}
