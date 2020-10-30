<?php

namespace MolliePrefix;

class BeforeAndAfterTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public static $beforeWasRun;
    public static $afterWasRun;
    public static function resetProperties()
    {
        self::$beforeWasRun = 0;
        self::$afterWasRun = 0;
    }
    /**
     * @before
     */
    public function initialSetup()
    {
        self::$beforeWasRun++;
    }
    /**
     * @after
     */
    public function finalTeardown()
    {
        self::$afterWasRun++;
    }
    public function test1()
    {
    }
    public function test2()
    {
    }
}
\class_alias('MolliePrefix\\BeforeAndAfterTest', 'MolliePrefix\\BeforeAndAfterTest', \false);
