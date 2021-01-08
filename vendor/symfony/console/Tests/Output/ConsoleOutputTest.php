<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Output;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter;
use MolliePrefix\Symfony\Component\Console\Output\ConsoleOutput;
use MolliePrefix\Symfony\Component\Console\Output\Output;
class ConsoleOutputTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $output = new \MolliePrefix\Symfony\Component\Console\Output\ConsoleOutput(\MolliePrefix\Symfony\Component\Console\Output\Output::VERBOSITY_QUIET, \true);
        $this->assertEquals(\MolliePrefix\Symfony\Component\Console\Output\Output::VERBOSITY_QUIET, $output->getVerbosity(), '__construct() takes the verbosity as its first argument');
        $this->assertSame($output->getFormatter(), $output->getErrorOutput()->getFormatter(), '__construct() takes a formatter or null as the third argument');
    }
    public function testSetFormatter()
    {
        $output = new \MolliePrefix\Symfony\Component\Console\Output\ConsoleOutput();
        $outputFormatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter();
        $output->setFormatter($outputFormatter);
        $this->assertSame($outputFormatter, $output->getFormatter());
    }
    public function testSetVerbosity()
    {
        $output = new \MolliePrefix\Symfony\Component\Console\Output\ConsoleOutput();
        $output->setVerbosity(\MolliePrefix\Symfony\Component\Console\Output\Output::VERBOSITY_VERBOSE);
        $this->assertSame(\MolliePrefix\Symfony\Component\Console\Output\Output::VERBOSITY_VERBOSE, $output->getVerbosity());
    }
}
