<?php

namespace MolliePrefix;

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Runner_BaseTestRunnerTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testInvokeNonStaticSuite()
    {
        $runner = new \MolliePrefix\MockRunner();
        $runner->getTest('NonStatic');
    }
}
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
\class_alias('MolliePrefix\\Runner_BaseTestRunnerTest', 'MolliePrefix\\Runner_BaseTestRunnerTest', \false);
