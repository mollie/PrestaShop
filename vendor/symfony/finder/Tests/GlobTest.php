<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Finder\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Finder\Finder;
use MolliePrefix\Symfony\Component\Finder\Glob;
class GlobTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testGlobToRegexDelimiters()
    {
        $this->assertEquals('#^(?=[^\\.])\\#$#', \MolliePrefix\Symfony\Component\Finder\Glob::toRegex('#'));
        $this->assertEquals('#^\\.[^/]*$#', \MolliePrefix\Symfony\Component\Finder\Glob::toRegex('.*'));
        $this->assertEquals('^\\.[^/]*$', \MolliePrefix\Symfony\Component\Finder\Glob::toRegex('.*', \true, \true, ''));
        $this->assertEquals('/^\\.[^/]*$/', \MolliePrefix\Symfony\Component\Finder\Glob::toRegex('.*', \true, \true, '/'));
    }
    public function testGlobToRegexDoubleStarStrictDots()
    {
        $finder = new \MolliePrefix\Symfony\Component\Finder\Finder();
        $finder->ignoreDotFiles(\false);
        $regex = \MolliePrefix\Symfony\Component\Finder\Glob::toRegex('/**/*.neon');
        foreach ($finder->in(__DIR__) as $k => $v) {
            $k = \str_replace(\DIRECTORY_SEPARATOR, '/', $k);
            if (\preg_match($regex, \substr($k, \strlen(__DIR__)))) {
                $match[] = \substr($k, 10 + \strlen(__DIR__));
            }
        }
        \sort($match);
        $this->assertSame(['one/b/c.neon', 'one/b/d.neon'], $match);
    }
    public function testGlobToRegexDoubleStarNonStrictDots()
    {
        $finder = new \MolliePrefix\Symfony\Component\Finder\Finder();
        $finder->ignoreDotFiles(\false);
        $regex = \MolliePrefix\Symfony\Component\Finder\Glob::toRegex('/**/*.neon', \false);
        foreach ($finder->in(__DIR__) as $k => $v) {
            $k = \str_replace(\DIRECTORY_SEPARATOR, '/', $k);
            if (\preg_match($regex, \substr($k, \strlen(__DIR__)))) {
                $match[] = \substr($k, 10 + \strlen(__DIR__));
            }
        }
        \sort($match);
        $this->assertSame(['.dot/b/c.neon', '.dot/b/d.neon', 'one/b/c.neon', 'one/b/d.neon'], $match);
    }
    public function testGlobToRegexDoubleStarWithoutLeadingSlash()
    {
        $finder = new \MolliePrefix\Symfony\Component\Finder\Finder();
        $finder->ignoreDotFiles(\false);
        $regex = \MolliePrefix\Symfony\Component\Finder\Glob::toRegex('/Fixtures/one/**');
        foreach ($finder->in(__DIR__) as $k => $v) {
            $k = \str_replace(\DIRECTORY_SEPARATOR, '/', $k);
            if (\preg_match($regex, \substr($k, \strlen(__DIR__)))) {
                $match[] = \substr($k, 10 + \strlen(__DIR__));
            }
        }
        \sort($match);
        $this->assertSame(['one/a', 'one/b', 'one/b/c.neon', 'one/b/d.neon'], $match);
    }
    public function testGlobToRegexDoubleStarWithoutLeadingSlashNotStrictLeadingDot()
    {
        $finder = new \MolliePrefix\Symfony\Component\Finder\Finder();
        $finder->ignoreDotFiles(\false);
        $regex = \MolliePrefix\Symfony\Component\Finder\Glob::toRegex('/Fixtures/one/**', \false);
        foreach ($finder->in(__DIR__) as $k => $v) {
            $k = \str_replace(\DIRECTORY_SEPARATOR, '/', $k);
            if (\preg_match($regex, \substr($k, \strlen(__DIR__)))) {
                $match[] = \substr($k, 10 + \strlen(__DIR__));
            }
        }
        \sort($match);
        $this->assertSame(['one/.dot', 'one/a', 'one/b', 'one/b/c.neon', 'one/b/d.neon'], $match);
    }
}
