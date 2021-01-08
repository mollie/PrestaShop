<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Helper;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Console\Helper\TableStyle;
class TableStyleTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testSetPadTypeWithInvalidType()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid padding type. Expected one of (STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH).');
        $style = new \MolliePrefix\Symfony\Component\Console\Helper\TableStyle();
        $style->setPadType('TEST');
    }
}
