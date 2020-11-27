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

use MolliePrefix\Symfony\Component\Finder\Comparator\DateComparator;
use MolliePrefix\Symfony\Component\Finder\Iterator\DateRangeFilterIterator;
class DateRangeFilterIteratorTest extends \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\RealIteratorTestCase
{
    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($size, $expected)
    {
        $files = self::$files;
        $files[] = self::toAbsolute('doesnotexist');
        $inner = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\Iterator($files);
        $iterator = new \MolliePrefix\Symfony\Component\Finder\Iterator\DateRangeFilterIterator($inner, $size);
        $this->assertIterator($expected, $iterator);
    }
    public function getAcceptData()
    {
        $since20YearsAgo = ['.git', 'test.py', 'foo', 'foo/bar.tmp', 'test.php', 'toto', 'toto/.git', '.bar', '.foo', '.foo/.bar', 'foo bar', '.foo/bar'];
        $since2MonthsAgo = ['.git', 'test.py', 'foo', 'toto', 'toto/.git', '.bar', '.foo', '.foo/.bar', 'foo bar', '.foo/bar'];
        $untilLastMonth = ['foo/bar.tmp', 'test.php'];
        return [[[new \MolliePrefix\Symfony\Component\Finder\Comparator\DateComparator('since 20 years ago')], $this->toAbsolute($since20YearsAgo)], [[new \MolliePrefix\Symfony\Component\Finder\Comparator\DateComparator('since 2 months ago')], $this->toAbsolute($since2MonthsAgo)], [[new \MolliePrefix\Symfony\Component\Finder\Comparator\DateComparator('until last month')], $this->toAbsolute($untilLastMonth)]];
    }
}
