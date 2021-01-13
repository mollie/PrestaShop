<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Output;

use MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter;
use MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterInterface;
/**
 * NullOutput suppresses all output.
 *
 *     $output = new NullOutput();
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 */
class NullOutput implements \MolliePrefix\Symfony\Component\Console\Output\OutputInterface
{
    /**
     * {@inheritdoc}
     */
    public function setFormatter(\MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterInterface $formatter)
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        // to comply with the interface we must return a OutputFormatterInterface
        return new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter();
    }
    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     */
    public function isDecorated()
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function setVerbosity($level)
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     */
    public function getVerbosity()
    {
        return self::VERBOSITY_QUIET;
    }
    /**
     * {@inheritdoc}
     */
    public function isQuiet()
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function isVerbose()
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function isVeryVerbose()
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function isDebug()
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function writeln($messages, $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = \false, $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }
}
