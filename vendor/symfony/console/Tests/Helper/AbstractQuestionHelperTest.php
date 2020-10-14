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
use MolliePrefix\Symfony\Component\Console\Input\StreamableInputInterface;
abstract class AbstractQuestionHelperTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    protected function createStreamableInputInterfaceMock($stream = null, $interactive = \true)
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Symfony\Component\Console\Input\StreamableInputInterface::class)->getMock();
        $mock->expects($this->any())->method('isInteractive')->willReturn($interactive);
        if ($stream) {
            $mock->expects($this->any())->method('getStream')->willReturn($stream);
        }
        return $mock;
    }
}
