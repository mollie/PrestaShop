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
class Framework_TestFailureTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $test = new self(__FUNCTION__);
        $exception = new \MolliePrefix\PHPUnit_Framework_Exception('message');
        $failure = new \MolliePrefix\PHPUnit_Framework_TestFailure($test, $exception);
        $this->assertEquals(__METHOD__ . ': message', $failure->toString());
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
\class_alias('MolliePrefix\\Framework_TestFailureTest', 'MolliePrefix\\Framework_TestFailureTest', \false);
