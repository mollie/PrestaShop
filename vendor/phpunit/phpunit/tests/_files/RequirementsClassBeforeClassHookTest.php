<?php

namespace MolliePrefix;

/**
 * @requires extension nonExistingExtension
 */
class RequirementsClassBeforeClassHookTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        throw new \Exception(__METHOD__ . ' should not be called because of class requirements.');
    }
}
/**
 * @requires extension nonExistingExtension
 */
\class_alias('MolliePrefix\\RequirementsClassBeforeClassHookTest', 'RequirementsClassBeforeClassHookTest', \false);
