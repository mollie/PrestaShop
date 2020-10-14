<?php

namespace MolliePrefix;

/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Stubs a method by returning the current object.
 *
 * @since Class available since Release 1.1.0
 */
class PHPUnit_Framework_MockObject_Stub_ReturnSelf implements \MolliePrefix\PHPUnit_Framework_MockObject_Stub
{
    public function invoke(\MolliePrefix\PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        if (!$invocation instanceof \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Object) {
            throw new \MolliePrefix\PHPUnit_Framework_MockObject_RuntimeException('The current object can only be returned when mocking an ' . 'object, not a static class.');
        }
        return $invocation->object;
    }
    public function toString()
    {
        return 'return the current object';
    }
}
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Stubs a method by returning the current object.
 *
 * @since Class available since Release 1.1.0
 */
\class_alias('MolliePrefix\\PHPUnit_Framework_MockObject_Stub_ReturnSelf', 'PHPUnit_Framework_MockObject_Stub_ReturnSelf', \false);
