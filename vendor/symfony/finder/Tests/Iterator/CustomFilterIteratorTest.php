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

use MolliePrefix\Symfony\Component\Finder\Iterator\CustomFilterIterator;
class CustomFilterIteratorTest extends \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\IteratorTestCase
{
    public function testWithInvalidFilter()
    {
        $this->expectException('InvalidArgumentException');
        new \MolliePrefix\Symfony\Component\Finder\Iterator\CustomFilterIterator(new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\Iterator(), ['foo']);
    }
    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($filters, $expected)
    {
        $inner = new \MolliePrefix\Symfony\Component\Finder\Tests\Iterator\Iterator(['test.php', 'test.py', 'foo.php']);
        $iterator = new \MolliePrefix\Symfony\Component\Finder\Iterator\CustomFilterIterator($inner, $filters);
        $this->assertIterator($expected, $iterator);
    }
    public function getAcceptData()
    {
        return [[[function (\SplFileInfo $fileinfo) {
            return \false;
        }], []], [[function (\SplFileInfo $fileinfo) {
            return 0 === \strpos($fileinfo, 'test');
        }], ['test.php', 'test.py']], [['is_dir'], []]];
    }
}
