<?php

/*
 * This file is part of the GlobalState package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\SebastianBergmann\GlobalState;

use MolliePrefix\PHPUnit_Framework_TestCase;
/**
 */
class BlacklistTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @var \SebastianBergmann\GlobalState\Blacklist
     */
    private $blacklist;
    protected function setUp()
    {
        $this->blacklist = new \MolliePrefix\SebastianBergmann\GlobalState\Blacklist();
    }
    public function testGlobalVariableThatIsNotBlacklistedIsNotTreatedAsBlacklisted()
    {
        $this->assertFalse($this->blacklist->isGlobalVariableBlacklisted('variable'));
    }
    public function testGlobalVariableCanBeBlacklisted()
    {
        $this->blacklist->addGlobalVariable('variable');
        $this->assertTrue($this->blacklist->isGlobalVariableBlacklisted('variable'));
    }
    public function testStaticAttributeThatIsNotBlacklistedIsNotTreatedAsBlacklisted()
    {
        $this->assertFalse($this->blacklist->isStaticAttributeBlacklisted('MolliePrefix\\SebastianBergmann\\GlobalState\\TestFixture\\BlacklistedClass', 'attribute'));
    }
    public function testClassCanBeBlacklisted()
    {
        $this->blacklist->addClass('MolliePrefix\\SebastianBergmann\\GlobalState\\TestFixture\\BlacklistedClass');
        $this->assertTrue($this->blacklist->isStaticAttributeBlacklisted('MolliePrefix\\SebastianBergmann\\GlobalState\\TestFixture\\BlacklistedClass', 'attribute'));
    }
    public function testSubclassesCanBeBlacklisted()
    {
        $this->blacklist->addSubclassesOf('MolliePrefix\\SebastianBergmann\\GlobalState\\TestFixture\\BlacklistedClass');
        $this->assertTrue($this->blacklist->isStaticAttributeBlacklisted('MolliePrefix\\SebastianBergmann\\GlobalState\\TestFixture\\BlacklistedChildClass', 'attribute'));
    }
    public function testImplementorsCanBeBlacklisted()
    {
        $this->blacklist->addImplementorsOf('MolliePrefix\\SebastianBergmann\\GlobalState\\TestFixture\\BlacklistedInterface');
        $this->assertTrue($this->blacklist->isStaticAttributeBlacklisted('MolliePrefix\\SebastianBergmann\\GlobalState\\TestFixture\\BlacklistedImplementor', 'attribute'));
    }
    public function testClassNamePrefixesCanBeBlacklisted()
    {
        $this->blacklist->addClassNamePrefix('MolliePrefix\\SebastianBergmann\\GlobalState');
        $this->assertTrue($this->blacklist->isStaticAttributeBlacklisted('MolliePrefix\\SebastianBergmann\\GlobalState\\TestFixture\\BlacklistedClass', 'attribute'));
    }
    public function testStaticAttributeCanBeBlacklisted()
    {
        $this->blacklist->addStaticAttribute('MolliePrefix\\SebastianBergmann\\GlobalState\\TestFixture\\BlacklistedClass', 'attribute');
        $this->assertTrue($this->blacklist->isStaticAttributeBlacklisted('MolliePrefix\\SebastianBergmann\\GlobalState\\TestFixture\\BlacklistedClass', 'attribute'));
    }
}
