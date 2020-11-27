<?php

namespace MolliePrefix\PhpParser\Parser;

use MolliePrefix\PhpParser\Lexer;
use MolliePrefix\PhpParser\ParserTest;
require_once __DIR__ . '/../ParserTest.php';
class Php7Test extends \MolliePrefix\PhpParser\ParserTest
{
    protected function getParser(\MolliePrefix\PhpParser\Lexer $lexer)
    {
        return new \MolliePrefix\PhpParser\Parser\Php7($lexer);
    }
}
