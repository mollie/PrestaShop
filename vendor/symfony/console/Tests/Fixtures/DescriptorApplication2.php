<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Fixtures;

use MolliePrefix\Symfony\Component\Console\Application;
class DescriptorApplication2 extends \MolliePrefix\Symfony\Component\Console\Application
{
    public function __construct()
    {
        parent::__construct('My Symfony application', 'v1.0');
        $this->add(new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorCommand1());
        $this->add(new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorCommand2());
        $this->add(new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorCommand3());
        $this->add(new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorCommand4());
    }
}
