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

use MolliePrefix\Symfony\Component\Finder\Iterator\ExcludeDirectoryFilterIterator;
use MolliePrefix\Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
class ExcludeDirectoryFilterIteratorTest extends \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\RealIteratorTestCase
{
    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($directories, $expected)
    {
        $inner = new \RecursiveIteratorIterator(new \MolliePrefix\Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator($this->toAbsolute(), \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        $iterator = new \MolliePrefix\Symfony\Component\Finder\Iterator\ExcludeDirectoryFilterIterator($inner, $directories);
        $this->assertIterator($expected, $iterator);
    }
    public function getAcceptData()
    {
        $foo = ['.bar', '.foo', '.foo/.bar', '.foo/bar', '.git', 'test.py', 'test.php', 'toto', 'toto/.git', 'foo bar'];
        $fo = ['.bar', '.foo', '.foo/.bar', '.foo/bar', '.git', 'test.py', 'foo', 'foo/bar.tmp', 'test.php', 'toto', 'toto/.git', 'foo bar'];
        $toto = ['.bar', '.foo', '.foo/.bar', '.foo/bar', '.git', 'test.py', 'foo', 'foo/bar.tmp', 'test.php', 'foo bar'];
        return [[['foo'], $this->toAbsolute($foo)], [['fo'], $this->toAbsolute($fo)], [['toto/'], $this->toAbsolute($toto)]];
    }
}
