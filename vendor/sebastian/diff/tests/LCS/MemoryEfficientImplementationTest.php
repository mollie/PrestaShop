<?php

/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\SebastianBergmann\Diff\LCS;

/**
 * @covers SebastianBergmann\Diff\LCS\MemoryEfficientImplementation
 */
class MemoryEfficientImplementationTest extends \MolliePrefix\SebastianBergmann\Diff\LCS\LongestCommonSubsequenceTest
{
    protected function createImplementation()
    {
        return new \MolliePrefix\SebastianBergmann\Diff\LCS\MemoryEfficientImplementation();
    }
}
