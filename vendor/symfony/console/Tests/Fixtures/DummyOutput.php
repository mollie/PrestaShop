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

use MolliePrefix\Symfony\Component\Console\Output\BufferedOutput;
/**
 * Dummy output.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class DummyOutput extends \MolliePrefix\Symfony\Component\Console\Output\BufferedOutput
{
    /**
     * @return array
     */
    public function getLogs()
    {
        $logs = [];
        foreach (\explode(\PHP_EOL, \trim($this->fetch())) as $message) {
            \preg_match('/^\\[(.*)\\] (.*)/', $message, $matches);
            $logs[] = \sprintf('%s %s', $matches[1], $matches[2]);
        }
        return $logs;
    }
}
