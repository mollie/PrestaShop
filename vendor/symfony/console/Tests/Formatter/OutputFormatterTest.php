<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Formatter;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter;
use MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle;
class OutputFormatterTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testEmptyTag()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $this->assertEquals('foo<>bar', $formatter->format('foo<>bar'));
    }
    public function testLGCharEscaping()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $this->assertEquals('foo<bar', $formatter->format('foo\\<bar'));
        $this->assertEquals('foo << bar', $formatter->format('foo << bar'));
        $this->assertEquals('foo << bar \\', $formatter->format('foo << bar \\'));
        $this->assertEquals("foo << \33[32mbar \\ baz\33[39m \\", $formatter->format('foo << <info>bar \\ baz</info> \\'));
        $this->assertEquals('<info>some info</info>', $formatter->format('\\<info>some info\\</info>'));
        $this->assertEquals('\\<info>some info\\</info>', \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter::escape('<info>some info</info>'));
        $this->assertEquals("\33[33mSymfony\\Component\\Console does work very well!\33[39m", $formatter->format('<comment>Symfony\\Component\\Console does work very well!</comment>'));
    }
    public function testBundledStyles()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $this->assertTrue($formatter->hasStyle('error'));
        $this->assertTrue($formatter->hasStyle('info'));
        $this->assertTrue($formatter->hasStyle('comment'));
        $this->assertTrue($formatter->hasStyle('question'));
        $this->assertEquals("\33[37;41msome error\33[39;49m", $formatter->format('<error>some error</error>'));
        $this->assertEquals("\33[32msome info\33[39m", $formatter->format('<info>some info</info>'));
        $this->assertEquals("\33[33msome comment\33[39m", $formatter->format('<comment>some comment</comment>'));
        $this->assertEquals("\33[30;46msome question\33[39;49m", $formatter->format('<question>some question</question>'));
    }
    public function testNestedStyles()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $this->assertEquals("\33[37;41msome \33[39;49m\33[32msome info\33[39m\33[37;41m error\33[39;49m", $formatter->format('<error>some <info>some info</info> error</error>'));
    }
    public function testAdjacentStyles()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $this->assertEquals("\33[37;41msome error\33[39;49m\33[32msome info\33[39m", $formatter->format('<error>some error</error><info>some info</info>'));
    }
    public function testStyleMatchingNotGreedy()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $this->assertEquals("(\33[32m>=2.0,<2.3\33[39m)", $formatter->format('(<info>>=2.0,<2.3</info>)'));
    }
    public function testStyleEscaping()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $this->assertEquals("(\33[32mz>=2.0,<<<a2.3\\\33[39m)", $formatter->format('(<info>' . $formatter->escape('z>=2.0,<\\<<a2.3\\') . '</info>)'));
        $this->assertEquals("\33[32m<error>some error</error>\33[39m", $formatter->format('<info>' . $formatter->escape('<error>some error</error>') . '</info>'));
    }
    public function testDeepNestedStyles()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $this->assertEquals("\33[37;41merror\33[39;49m\33[32minfo\33[39m\33[33mcomment\33[39m\33[37;41merror\33[39;49m", $formatter->format('<error>error<info>info<comment>comment</info>error</error>'));
    }
    public function testNewStyle()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $style = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('blue', 'white');
        $formatter->setStyle('test', $style);
        $this->assertEquals($style, $formatter->getStyle('test'));
        $this->assertNotEquals($style, $formatter->getStyle('info'));
        $style = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('blue', 'white');
        $formatter->setStyle('b', $style);
        $this->assertEquals("\33[34;47msome \33[39;49m\33[34;47mcustom\33[39;49m\33[34;47m msg\33[39;49m", $formatter->format('<test>some <b>custom</b> msg</test>'));
    }
    public function testRedefineStyle()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $style = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('blue', 'white');
        $formatter->setStyle('info', $style);
        $this->assertEquals("\33[34;47msome custom msg\33[39;49m", $formatter->format('<info>some custom msg</info>'));
    }
    public function testInlineStyle()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $this->assertEquals("\33[34;41msome text\33[39;49m", $formatter->format('<fg=blue;bg=red>some text</>'));
        $this->assertEquals("\33[34;41msome text\33[39;49m", $formatter->format('<fg=blue;bg=red>some text</fg=blue;bg=red>'));
    }
    /**
     * @param string      $tag
     * @param string|null $expected
     * @param string|null $input
     *
     * @dataProvider provideInlineStyleOptionsCases
     */
    public function testInlineStyleOptions($tag, $expected = null, $input = null)
    {
        $styleString = \substr($tag, 1, -1);
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $method = new \ReflectionMethod($formatter, 'createStyleFromString');
        $method->setAccessible(\true);
        $result = $method->invoke($formatter, $styleString);
        if (null === $expected) {
            $this->assertFalse($result);
            $expected = $tag . $input . '</' . $styleString . '>';
            $this->assertSame($expected, $formatter->format($expected));
        } else {
            /* @var OutputFormatterStyle $result */
            $this->assertInstanceOf(\MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle::class, $result);
            $this->assertSame($expected, $formatter->format($tag . $input . '</>'));
            $this->assertSame($expected, $formatter->format($tag . $input . '</' . $styleString . '>'));
        }
    }
    public function provideInlineStyleOptionsCases()
    {
        return [['<unknown=_unknown_>'], ['<unknown=_unknown_;a=1;b>'], ['<fg=green;>', "\33[32m[test]\33[39m", '[test]'], ['<fg=green;bg=blue;>', "\33[32;44ma\33[39;49m", 'a'], ['<fg=green;options=bold>', "\33[32;1mb\33[39;22m", 'b'], ['<fg=green;options=reverse;>', "\33[32;7m<a>\33[39;27m", '<a>'], ['<fg=green;options=bold,underscore>', "\33[32;1;4mz\33[39;22;24m", 'z'], ['<fg=green;options=bold,underscore,reverse;>', "\33[32;1;4;7md\33[39;22;24;27m", 'd']];
    }
    /**
     * @group legacy
     * @dataProvider provideInlineStyleTagsWithUnknownOptions
     * @expectedDeprecation Unknown style options are deprecated since Symfony 3.2 and will be removed in 4.0. Exception "Invalid option specified: "%s". Expected one of (bold, underscore, blink, reverse, conceal).".
     */
    public function testInlineStyleOptionsUnknownAreDeprecated($tag, $option)
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $formatter->format($tag);
    }
    public function provideInlineStyleTagsWithUnknownOptions()
    {
        return [['<options=abc;>', 'abc'], ['<options=abc,def;>', 'abc'], ['<fg=green;options=xyz;>', 'xyz'], ['<fg=green;options=efg,abc>', 'efg']];
    }
    public function testNonStyleTag()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $this->assertEquals("\33[32msome \33[39m\33[32m<tag>\33[39m\33[32m \33[39m\33[32m<setting=value>\33[39m\33[32m styled \33[39m\33[32m<p>\33[39m\33[32msingle-char tag\33[39m\33[32m</p>\33[39m", $formatter->format('<info>some <tag> <setting=value> styled <p>single-char tag</p></info>'));
    }
    public function testFormatLongString()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $long = \str_repeat('\\', 14000);
        $this->assertEquals("\33[37;41msome error\33[39;49m" . $long, $formatter->format('<error>some error</error>' . $long));
    }
    public function testFormatToStringObject()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\false);
        $this->assertEquals('some info', $formatter->format(new \MolliePrefix\Symfony\Component\Console\Tests\Formatter\TableCell()));
    }
    public function testNotDecoratedFormatter()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\false);
        $this->assertTrue($formatter->hasStyle('error'));
        $this->assertTrue($formatter->hasStyle('info'));
        $this->assertTrue($formatter->hasStyle('comment'));
        $this->assertTrue($formatter->hasStyle('question'));
        $this->assertEquals('some error', $formatter->format('<error>some error</error>'));
        $this->assertEquals('some info', $formatter->format('<info>some info</info>'));
        $this->assertEquals('some comment', $formatter->format('<comment>some comment</comment>'));
        $this->assertEquals('some question', $formatter->format('<question>some question</question>'));
        $this->assertEquals('some text with inline style', $formatter->format('<fg=red>some text with inline style</>'));
        $formatter->setDecorated(\true);
        $this->assertEquals("\33[37;41msome error\33[39;49m", $formatter->format('<error>some error</error>'));
        $this->assertEquals("\33[32msome info\33[39m", $formatter->format('<info>some info</info>'));
        $this->assertEquals("\33[33msome comment\33[39m", $formatter->format('<comment>some comment</comment>'));
        $this->assertEquals("\33[30;46msome question\33[39;49m", $formatter->format('<question>some question</question>'));
        $this->assertEquals("\33[31msome text with inline style\33[39m", $formatter->format('<fg=red>some text with inline style</>'));
    }
    public function testContentWithLineBreaks()
    {
        $formatter = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter(\true);
        $this->assertEquals(<<<EOF
\33[32m
some text\33[39m
EOF
, $formatter->format(<<<'EOF'
<info>
some text</info>
EOF
));
        $this->assertEquals(<<<EOF
\33[32msome text
\33[39m
EOF
, $formatter->format(<<<'EOF'
<info>some text
</info>
EOF
));
        $this->assertEquals(<<<EOF
\33[32m
some text
\33[39m
EOF
, $formatter->format(<<<'EOF'
<info>
some text
</info>
EOF
));
        $this->assertEquals(<<<EOF
\33[32m
some text
more text
\33[39m
EOF
, $formatter->format(<<<'EOF'
<info>
some text
more text
</info>
EOF
));
    }
}
class TableCell
{
    public function __toString()
    {
        return '<info>some info</info>';
    }
}
