<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\EventDispatcher\Tests;

use MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher;
class EventDispatcherTest extends \MolliePrefix\Symfony\Component\EventDispatcher\Tests\AbstractEventDispatcherTest
{
    protected function createEventDispatcher()
    {
        return new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher();
    }
}
