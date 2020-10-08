<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Descriptor;

use MolliePrefix\Symfony\Component\Console\Input\InputArgument;
use MolliePrefix\Symfony\Component\Console\Input\InputDefinition;
use MolliePrefix\Symfony\Component\Console\Input\InputOption;
use MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorApplication1;
use MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorApplication2;
use MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorCommand1;
use MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorCommand2;
/**
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class ObjectsProvider
{
    public static function getInputArguments()
    {
        return ['input_argument_1' => new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('argument_name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::REQUIRED), 'input_argument_2' => new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('argument_name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::IS_ARRAY, 'argument description'), 'input_argument_3' => new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('argument_name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'argument description', 'default_value'), 'input_argument_4' => new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('argument_name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::REQUIRED, "multiline\nargument description"), 'input_argument_with_style' => new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('argument_name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'argument description', '<comment>style</>'), 'input_argument_with_default_inf_value' => new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('argument_name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'argument description', \INF)];
    }
    public static function getInputOptions()
    {
        return ['input_option_1' => new \MolliePrefix\Symfony\Component\Console\Input\InputOption('option_name', 'o', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_NONE), 'input_option_2' => new \MolliePrefix\Symfony\Component\Console\Input\InputOption('option_name', 'o', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'option description', 'default_value'), 'input_option_3' => new \MolliePrefix\Symfony\Component\Console\Input\InputOption('option_name', 'o', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'option description'), 'input_option_4' => new \MolliePrefix\Symfony\Component\Console\Input\InputOption('option_name', 'o', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY | \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'option description', []), 'input_option_5' => new \MolliePrefix\Symfony\Component\Console\Input\InputOption('option_name', 'o', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, "multiline\noption description"), 'input_option_6' => new \MolliePrefix\Symfony\Component\Console\Input\InputOption('option_name', ['o', 'O'], \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'option with multiple shortcuts'), 'input_option_with_style' => new \MolliePrefix\Symfony\Component\Console\Input\InputOption('option_name', 'o', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'option description', '<comment>style</>'), 'input_option_with_style_array' => new \MolliePrefix\Symfony\Component\Console\Input\InputOption('option_name', 'o', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY | \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'option description', ['<comment>Hello</comment>', '<info>world</info>']), 'input_option_with_default_inf_value' => new \MolliePrefix\Symfony\Component\Console\Input\InputOption('option_name', 'o', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'option description', \INF)];
    }
    public static function getInputDefinitions()
    {
        return ['input_definition_1' => new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition(), 'input_definition_2' => new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('argument_name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::REQUIRED)]), 'input_definition_3' => new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputOption('option_name', 'o', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_NONE)]), 'input_definition_4' => new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('argument_name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::REQUIRED), new \MolliePrefix\Symfony\Component\Console\Input\InputOption('option_name', 'o', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_NONE)])];
    }
    public static function getCommands()
    {
        return ['command_1' => new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorCommand1(), 'command_2' => new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorCommand2()];
    }
    public static function getApplications()
    {
        return ['application_1' => new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorApplication1(), 'application_2' => new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorApplication2()];
    }
}
