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
class Util_RegexTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function validRegexpProvider()
    {
        return [['#valid regexp#', 'valid regexp', 1], [';val.*xp;', 'valid regexp', 1], ['/val.*xp/i', 'VALID REGEXP', 1], ['/a val.*p/', 'valid regexp', 0]];
    }
    public function invalidRegexpProvider()
    {
        return [['valid regexp', 'valid regexp'], [';val.*xp', 'valid regexp'], ['val.*xp/i', 'VALID REGEXP']];
    }
    /**
     * @dataProvider validRegexpProvider
     */
    public function testValidRegex($pattern, $subject, $return)
    {
        $this->assertEquals($return, \MolliePrefix\PHPUnit_Util_Regex::pregMatchSafe($pattern, $subject));
    }
    /**
     * @dataProvider invalidRegexpProvider
     */
    public function testInvalidRegex($pattern, $subject)
    {
        $this->assertFalse(\MolliePrefix\PHPUnit_Util_Regex::pregMatchSafe($pattern, $subject));
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
\class_alias('MolliePrefix\\Util_RegexTest', 'Util_RegexTest', \false);
