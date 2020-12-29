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
/**
 * Extension to PHPUnit_Framework_AssertionFailedError to mark the special
 * case of a test that printed output.
 *
 * @since Class available since Release 3.6.0
 */
class PHPUnit_Framework_OutputError extends \MolliePrefix\PHPUnit_Framework_AssertionFailedError
{
}
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Extension to PHPUnit_Framework_AssertionFailedError to mark the special
 * case of a test that printed output.
 *
 * @since Class available since Release 3.6.0
 */
\class_alias('MolliePrefix\\PHPUnit_Framework_OutputError', 'PHPUnit_Framework_OutputError', \false);
