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

use MolliePrefix\Symfony\Component\Console\Command\Command;
class DescriptorCommand1 extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('descriptor:command1')->setAliases(['alias1', 'alias2'])->setDescription('command 1 description')->setHelp('command 1 help');
    }
}
