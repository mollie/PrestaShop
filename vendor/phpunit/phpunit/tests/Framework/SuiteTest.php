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
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'BeforeAndAfterTest.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'BeforeClassAndAfterClassTest.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'TestWithTest.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'BeforeClassWithOnlyDataProviderTest.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'DataProviderSkippedTest.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'DataProviderDependencyTest.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'DataProviderIncompleteTest.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'InheritedTestCase.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'NoTestCaseClass.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'NoTestCases.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'NotPublicTestCase.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'NotVoidTestCase.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'OverrideTestCase.php';
require_once \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'RequirementsClassBeforeClassHookTest.php';
/**
 * @since      Class available since Release 2.0.0
 * @covers     PHPUnit_Framework_TestSuite
 */
class Framework_SuiteTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    protected $result;
    protected function setUp()
    {
        $this->result = new \MolliePrefix\PHPUnit_Framework_TestResult();
    }
    public static function suite()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite();
        $suite->addTest(new self('testAddTestSuite'));
        $suite->addTest(new self('testInheritedTests'));
        $suite->addTest(new self('testNoTestCases'));
        $suite->addTest(new self('testNoTestCaseClass'));
        $suite->addTest(new self('testNotExistingTestCase'));
        $suite->addTest(new self('testNotPublicTestCase'));
        $suite->addTest(new self('testNotVoidTestCase'));
        $suite->addTest(new self('testOneTestCase'));
        $suite->addTest(new self('testShadowedTests'));
        $suite->addTest(new self('testBeforeClassAndAfterClassAnnotations'));
        $suite->addTest(new self('testBeforeClassWithDataProviders'));
        $suite->addTest(new self('testBeforeAnnotation'));
        $suite->addTest(new self('testTestWithAnnotation'));
        $suite->addTest(new self('testSkippedTestDataProvider'));
        $suite->addTest(new self('testTestDataProviderDependency'));
        $suite->addTest(new self('testIncompleteTestDataProvider'));
        $suite->addTest(new self('testRequirementsBeforeClassHook'));
        $suite->addTest(new self('testDontSkipInheritedClass'));
        return $suite;
    }
    public function testAddTestSuite()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('OneTestCase');
        $suite->run($this->result);
        $this->assertCount(1, $this->result);
    }
    public function testInheritedTests()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('InheritedTestCase');
        $suite->run($this->result);
        $this->assertTrue($this->result->wasSuccessful());
        $this->assertCount(2, $this->result);
    }
    public function testNoTestCases()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('NoTestCases');
        $suite->run($this->result);
        $this->assertTrue(!$this->result->wasSuccessful());
        $this->assertEquals(0, $this->result->failureCount());
        $this->assertEquals(1, $this->result->warningCount());
        $this->assertCount(1, $this->result);
    }
    /**
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testNoTestCaseClass()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('NoTestCaseClass');
    }
    public function testNotExistingTestCase()
    {
        $suite = new self('notExistingMethod');
        $suite->run($this->result);
        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(1, $this->result->failureCount());
        $this->assertCount(1, $this->result);
    }
    public function testNotPublicTestCase()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('NotPublicTestCase');
        $this->assertCount(2, $suite);
    }
    public function testNotVoidTestCase()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('NotVoidTestCase');
        $this->assertCount(1, $suite);
    }
    public function testOneTestCase()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('OneTestCase');
        $suite->run($this->result);
        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(0, $this->result->failureCount());
        $this->assertCount(1, $this->result);
        $this->assertTrue($this->result->wasSuccessful());
    }
    public function testShadowedTests()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('OverrideTestCase');
        $suite->run($this->result);
        $this->assertCount(1, $this->result);
    }
    public function testBeforeClassAndAfterClassAnnotations()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('BeforeClassAndAfterClassTest');
        \MolliePrefix\BeforeClassAndAfterClassTest::resetProperties();
        $suite->run($this->result);
        $this->assertEquals(1, \MolliePrefix\BeforeClassAndAfterClassTest::$beforeClassWasRun, '@beforeClass method was not run once for the whole suite.');
        $this->assertEquals(1, \MolliePrefix\BeforeClassAndAfterClassTest::$afterClassWasRun, '@afterClass method was not run once for the whole suite.');
    }
    public function testBeforeClassWithDataProviders()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('BeforeClassWithOnlyDataProviderTest');
        \MolliePrefix\BeforeClassWithOnlyDataProviderTest::resetProperties();
        $suite->run($this->result);
        $this->assertTrue(\MolliePrefix\BeforeClassWithOnlyDataProviderTest::$setUpBeforeClassWasCalled, 'setUpBeforeClass method was not run.');
        $this->assertTrue(\MolliePrefix\BeforeClassWithOnlyDataProviderTest::$beforeClassWasCalled, '@beforeClass method was not run.');
    }
    public function testBeforeAnnotation()
    {
        $test = new \MolliePrefix\PHPUnit_Framework_TestSuite('BeforeAndAfterTest');
        \MolliePrefix\BeforeAndAfterTest::resetProperties();
        $result = $test->run();
        $this->assertEquals(2, \MolliePrefix\BeforeAndAfterTest::$beforeWasRun);
        $this->assertEquals(2, \MolliePrefix\BeforeAndAfterTest::$afterWasRun);
    }
    public function testTestWithAnnotation()
    {
        $test = new \MolliePrefix\PHPUnit_Framework_TestSuite('TestWithTest');
        \MolliePrefix\BeforeAndAfterTest::resetProperties();
        $result = $test->run();
        $this->assertCount(4, $result->passed());
    }
    public function testSkippedTestDataProvider()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('DataProviderSkippedTest');
        $suite->run($this->result);
        $this->assertEquals(3, $this->result->count());
        $this->assertEquals(1, $this->result->skippedCount());
    }
    public function testTestDataProviderDependency()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('DataProviderDependencyTest');
        $suite->run($this->result);
        $skipped = $this->result->skipped();
        $lastSkippedResult = \array_pop($skipped);
        $message = $lastSkippedResult->thrownException()->getMessage();
        $this->assertContains('Test for DataProviderDependencyTest::testDependency skipped by data provider', $message);
    }
    public function testIncompleteTestDataProvider()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('DataProviderIncompleteTest');
        $suite->run($this->result);
        $this->assertEquals(3, $this->result->count());
        $this->assertEquals(1, $this->result->notImplementedCount());
    }
    public function testRequirementsBeforeClassHook()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('RequirementsClassBeforeClassHookTest');
        $suite->run($this->result);
        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(1, $this->result->skippedCount());
    }
    public function testDontSkipInheritedClass()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('DontSkipInheritedClass');
        $dir = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . '_files' . \DIRECTORY_SEPARATOR . 'Inheritance' . \DIRECTORY_SEPARATOR;
        $suite->addTestFile($dir . 'InheritanceA.php');
        $suite->addTestFile($dir . 'InheritanceB.php');
        $result = $suite->run();
        $this->assertCount(2, $result);
    }
}
/**
 * @since      Class available since Release 2.0.0
 * @covers     PHPUnit_Framework_TestSuite
 */
\class_alias('MolliePrefix\\Framework_SuiteTest', 'Framework_SuiteTest', \false);
