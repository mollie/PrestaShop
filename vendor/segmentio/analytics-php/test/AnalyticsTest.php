<?php

namespace MolliePrefix;

require_once \dirname(__FILE__) . "/../lib/Segment.php";
class AnalyticsTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    function setUp()
    {
        \date_default_timezone_set("UTC");
        \MolliePrefix\Segment::init("oq0vdlg7yi", array("debug" => \true));
    }
    function testTrack()
    {
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "john", "event" => "Module PHP Event")));
    }
    function testGroup()
    {
        $this->assertTrue(\MolliePrefix\Segment::group(array("groupId" => "group-id", "userId" => "user-id", "traits" => array("plan" => "startup"))));
    }
    function testMicrotime()
    {
        $this->assertTrue(\MolliePrefix\Segment::page(array("anonymousId" => "anonymous-id", "name" => "analytics-php-microtime", "category" => "docs", "timestamp" => \microtime(\true), "properties" => array("path" => "/docs/libraries/php/", "url" => "https://segment.io/docs/libraries/php/"))));
    }
    function testPage()
    {
        $this->assertTrue(\MolliePrefix\Segment::page(array("anonymousId" => "anonymous-id", "name" => "analytics-php", "category" => "docs", "properties" => array("path" => "/docs/libraries/php/", "url" => "https://segment.io/docs/libraries/php/"))));
    }
    function testBasicPage()
    {
        $this->assertTrue(\MolliePrefix\Segment::page(array("anonymousId" => "anonymous-id")));
    }
    function testScreen()
    {
        $this->assertTrue(\MolliePrefix\Segment::screen(array("anonymousId" => "anonymous-id", "name" => "2048", "category" => "game built with php :)", "properties" => array("points" => 300))));
    }
    function testBasicScreen()
    {
        $this->assertTrue(\MolliePrefix\Segment::screen(array("anonymousId" => "anonymous-id")));
    }
    function testIdentify()
    {
        $this->assertTrue(\MolliePrefix\Segment::identify(array("userId" => "doe", "traits" => array("loves_php" => \false, "birthday" => \time()))));
    }
    function testEmptyTraits()
    {
        $this->assertTrue(\MolliePrefix\Segment::identify(array("userId" => "empty-traits")));
        $this->assertTrue(\MolliePrefix\Segment::group(array("userId" => "empty-traits", "groupId" => "empty-traits")));
    }
    function testEmptyArrayTraits()
    {
        $this->assertTrue(\MolliePrefix\Segment::identify(array("userId" => "empty-traits", "traits" => array())));
        $this->assertTrue(\MolliePrefix\Segment::group(array("userId" => "empty-traits", "groupId" => "empty-traits", "traits" => array())));
    }
    function testEmptyProperties()
    {
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "user-id", "event" => "empty-properties")));
        $this->assertTrue(\MolliePrefix\Segment::page(array("category" => "empty-properties", "name" => "empty-properties", "userId" => "user-id")));
    }
    function testEmptyArrayProperties()
    {
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "user-id", "event" => "empty-properties", "properties" => array())));
        $this->assertTrue(\MolliePrefix\Segment::page(array("category" => "empty-properties", "name" => "empty-properties", "userId" => "user-id", "properties" => array())));
    }
    function testAlias()
    {
        $this->assertTrue(\MolliePrefix\Segment::alias(array("previousId" => "previous-id", "userId" => "user-id")));
    }
    function testContextEmpty()
    {
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "user-id", "event" => "Context Test", "context" => array())));
    }
    function testContextCustom()
    {
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "user-id", "event" => "Context Test", "context" => array("active" => \false))));
    }
    function testTimestamps()
    {
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "user-id", "event" => "integer-timestamp", "timestamp" => (int) \mktime(0, 0, 0, \date('n'), 1, \date('Y')))));
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "user-id", "event" => "string-integer-timestamp", "timestamp" => (string) \mktime(0, 0, 0, \date('n'), 1, \date('Y')))));
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "user-id", "event" => "iso8630-timestamp", "timestamp" => \date(\DATE_ATOM, \mktime(0, 0, 0, \date('n'), 1, \date('Y'))))));
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "user-id", "event" => "iso8601-timestamp", "timestamp" => \date(\DATE_ATOM, \mktime(0, 0, 0, \date('n'), 1, \date('Y'))))));
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "user-id", "event" => "strtotime-timestamp", "timestamp" => \strtotime('1 week ago'))));
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "user-id", "event" => "microtime-timestamp", "timestamp" => \microtime(\true))));
        $this->assertTrue(\MolliePrefix\Segment::track(array("userId" => "user-id", "event" => "invalid-float-timestamp", "timestamp" => (string) \mktime(0, 0, 0, \date('n'), 1, \date('Y')) . '.')));
    }
}
\class_alias('MolliePrefix\\AnalyticsTest', 'AnalyticsTest', \false);
