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
use MolliePrefix\Symfony\Component\Console\Input\InputArgument;
use MolliePrefix\Symfony\Component\Console\Input\InputOption;
class DescriptorCommand2 extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('descriptor:command2')->setDescription('command 2 description')->setHelp('command 2 help')->addUsage('-o|--option_name <argument_name>')->addUsage('<argument_name>')->addArgument('argument_name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::REQUIRED)->addOption('option_name', 'o', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_NONE);
    }
}
