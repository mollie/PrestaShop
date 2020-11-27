<?php

namespace MolliePrefix\PhpParser;

class CommentTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testGetSet()
    {
        $comment = new \MolliePrefix\PhpParser\Comment('/* Some comment */', 1, 10);
        $this->assertSame('/* Some comment */', $comment->getText());
        $this->assertSame('/* Some comment */', (string) $comment);
        $this->assertSame(1, $comment->getLine());
        $this->assertSame(10, $comment->getFilePos());
    }
    /**
     * @dataProvider provideTestReformatting
     */
    public function testReformatting($commentText, $reformattedText)
    {
        $comment = new \MolliePrefix\PhpParser\Comment($commentText);
        $this->assertSame($reformattedText, $comment->getReformattedText());
    }
    public function provideTestReformatting()
    {
        return array(
            array('// Some text' . "\n", '// Some text'),
            array('/* Some text */', '/* Some text */'),
            array('/**
     * Some text.
     * Some more text.
     */', '/**
 * Some text.
 * Some more text.
 */'),
            array('/*
        Some text.
        Some more text.
    */', '/*
    Some text.
    Some more text.
*/'),
            array('/* Some text.
       More text.
       Even more text. */', '/* Some text.
   More text.
   Even more text. */'),
            array('/* Some text.
       More text.
         Indented text. */', '/* Some text.
   More text.
     Indented text. */'),
            // invalid comment -> no reformatting
            array('hallo
    world', 'hallo
    world'),
        );
    }
}
