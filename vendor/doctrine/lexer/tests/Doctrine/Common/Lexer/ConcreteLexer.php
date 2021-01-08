<?php

namespace MolliePrefix\Doctrine\Tests\Common\Lexer;

use MolliePrefix\Doctrine\Common\Lexer\AbstractLexer;
class ConcreteLexer extends \MolliePrefix\Doctrine\Common\Lexer\AbstractLexer
{
    const INT = 'int';
    protected function getCatchablePatterns()
    {
        return array('=|<|>', '[a-z]+', '\\d+');
    }
    protected function getNonCatchablePatterns()
    {
        return array('\\s+', '(.)');
    }
    protected function getType(&$value)
    {
        if (\is_numeric($value)) {
            $value = (int) $value;
            return 'int';
        }
        if (\in_array($value, array('=', '<', '>'))) {
            return 'operator';
        }
        if (\is_string($value)) {
            return 'string';
        }
        return;
    }
    protected function getModifiers()
    {
        return parent::getModifiers() . 'u';
    }
}
