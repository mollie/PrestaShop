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

use MolliePrefix\Symfony\Component\Finder\Iterator\FilenameFilterIterator;
class FilenameFilterIteratorTest extends \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\IteratorTestCase
{
    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($matchPatterns, $noMatchPatterns, $expected)
    {
        $inner = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\InnerNameIterator(['test.php', 'test.py', 'foo.php']);
        $iterator = new \MolliePrefix\Symfony\Component\Finder\Iterator\FilenameFilterIterator($inner, $matchPatterns, $noMatchPatterns);
        $this->assertIterator($expected, $iterator);
    }
    public function getAcceptData()
    {
        return [[['test.*'], [], ['test.php', 'test.py']], [[], ['test.*'], ['foo.php']], [['*.php'], ['test.*'], ['foo.php']], [['*.php', '*.py'], ['foo.*'], ['test.php', 'test.py']], [['/\\.php$/'], [], ['test.php', 'foo.php']], [[], ['/\\.php$/'], ['test.py']]];
    }
}
class InnerNameIterator extends \ArrayIterator
{
    public function current()
    {
        return new \SplFileInfo(parent::current());
    }
    public function getFilename()
    {
        return parent::current();
    }
}
