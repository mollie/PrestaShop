<?php

namespace MolliePrefix;

/*
 * This file is part of the PHP_TokenStream package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * A PHP token.
 *
 * @author    Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link      http://github.com/sebastianbergmann/php-token-stream/tree
 * @since     Class available since Release 1.0.0
 */
abstract class PHP_Token
{
    /**
     * @var string
     */
    protected $text;
    /**
     * @var integer
     */
    protected $line;
    /**
     * @var PHP_Token_Stream
     */
    protected $tokenStream;
    /**
     * @var integer
     */
    protected $id;
    /**
     * Constructor.
     *
     * @param string           $text
     * @param integer          $line
     * @param PHP_Token_Stream $tokenStream
     * @param integer          $id
     */
    public function __construct($text, $line, \MolliePrefix\PHP_Token_Stream $tokenStream, $id)
    {
        $this->text = $text;
        $this->line = $line;
        $this->tokenStream = $tokenStream;
        $this->id = $id;
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->text;
    }
    /**
     * @return integer
     */
    public function getLine()
    {
        return $this->line;
    }
}
/*
 * This file is part of the PHP_TokenStream package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * A PHP token.
 *
 * @author    Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link      http://github.com/sebastianbergmann/php-token-stream/tree
 * @since     Class available since Release 1.0.0
 */
\class_alias('MolliePrefix\\PHP_Token', 'PHP_Token', \false);
abstract class PHP_TokenWithScope extends \MolliePrefix\PHP_Token
{
    /**
     * @var integer
     */
    protected $endTokenId;
    /**
     * Get the docblock for this token
     *
     * This method will fetch the docblock belonging to the current token. The
     * docblock must be placed on the line directly above the token to be
     * recognized.
     *
     * @return string|null Returns the docblock as a string if found
     */
    public function getDocblock()
    {
        $tokens = $this->tokenStream->tokens();
        $currentLineNumber = $tokens[$this->id]->getLine();
        $prevLineNumber = $currentLineNumber - 1;
        for ($i = $this->id - 1; $i; $i--) {
            if (!isset($tokens[$i])) {
                return;
            }
            if ($tokens[$i] instanceof \MolliePrefix\PHP_Token_FUNCTION || $tokens[$i] instanceof \MolliePrefix\PHP_Token_CLASS || $tokens[$i] instanceof \MolliePrefix\PHP_Token_TRAIT) {
                // Some other trait, class or function, no docblock can be
                // used for the current token
                break;
            }
            $line = $tokens[$i]->getLine();
            if ($line == $currentLineNumber || $line == $prevLineNumber && $tokens[$i] instanceof \MolliePrefix\PHP_Token_WHITESPACE) {
                continue;
            }
            if ($line < $currentLineNumber && !$tokens[$i] instanceof \MolliePrefix\PHP_Token_DOC_COMMENT) {
                break;
            }
            return (string) $tokens[$i];
        }
    }
    /**
     * @return integer
     */
    public function getEndTokenId()
    {
        $block = 0;
        $i = $this->id;
        $tokens = $this->tokenStream->tokens();
        while ($this->endTokenId === null && isset($tokens[$i])) {
            if ($tokens[$i] instanceof \MolliePrefix\PHP_Token_OPEN_CURLY || $tokens[$i] instanceof \MolliePrefix\PHP_Token_CURLY_OPEN) {
                $block++;
            } elseif ($tokens[$i] instanceof \MolliePrefix\PHP_Token_CLOSE_CURLY) {
                $block--;
                if ($block === 0) {
                    $this->endTokenId = $i;
                }
            } elseif (($this instanceof \MolliePrefix\PHP_Token_FUNCTION || $this instanceof \MolliePrefix\PHP_Token_NAMESPACE) && $tokens[$i] instanceof \MolliePrefix\PHP_Token_SEMICOLON) {
                if ($block === 0) {
                    $this->endTokenId = $i;
                }
            }
            $i++;
        }
        if ($this->endTokenId === null) {
            $this->endTokenId = $this->id;
        }
        return $this->endTokenId;
    }
    /**
     * @return integer
     */
    public function getEndLine()
    {
        return $this->tokenStream[$this->getEndTokenId()]->getLine();
    }
}
\class_alias('MolliePrefix\\PHP_TokenWithScope', 'PHP_TokenWithScope', \false);
abstract class PHP_TokenWithScopeAndVisibility extends \MolliePrefix\PHP_TokenWithScope
{
    /**
     * @return string
     */
    public function getVisibility()
    {
        $tokens = $this->tokenStream->tokens();
        for ($i = $this->id - 2; $i > $this->id - 7; $i -= 2) {
            if (isset($tokens[$i]) && ($tokens[$i] instanceof \MolliePrefix\PHP_Token_PRIVATE || $tokens[$i] instanceof \MolliePrefix\PHP_Token_PROTECTED || $tokens[$i] instanceof \MolliePrefix\PHP_Token_PUBLIC)) {
                return \strtolower(\str_replace('PHP_Token_', '', \get_class($tokens[$i])));
            }
            if (isset($tokens[$i]) && !($tokens[$i] instanceof \MolliePrefix\PHP_Token_STATIC || $tokens[$i] instanceof \MolliePrefix\PHP_Token_FINAL || $tokens[$i] instanceof \MolliePrefix\PHP_Token_ABSTRACT)) {
                // no keywords; stop visibility search
                break;
            }
        }
    }
    /**
     * @return string
     */
    public function getKeywords()
    {
        $keywords = array();
        $tokens = $this->tokenStream->tokens();
        for ($i = $this->id - 2; $i > $this->id - 7; $i -= 2) {
            if (isset($tokens[$i]) && ($tokens[$i] instanceof \MolliePrefix\PHP_Token_PRIVATE || $tokens[$i] instanceof \MolliePrefix\PHP_Token_PROTECTED || $tokens[$i] instanceof \MolliePrefix\PHP_Token_PUBLIC)) {
                continue;
            }
            if (isset($tokens[$i]) && ($tokens[$i] instanceof \MolliePrefix\PHP_Token_STATIC || $tokens[$i] instanceof \MolliePrefix\PHP_Token_FINAL || $tokens[$i] instanceof \MolliePrefix\PHP_Token_ABSTRACT)) {
                $keywords[] = \strtolower(\str_replace('PHP_Token_', '', \get_class($tokens[$i])));
            }
        }
        return \implode(',', $keywords);
    }
}
\class_alias('MolliePrefix\\PHP_TokenWithScopeAndVisibility', 'PHP_TokenWithScopeAndVisibility', \false);
abstract class PHP_Token_Includes extends \MolliePrefix\PHP_Token
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $type;
    /**
     * @return string
     */
    public function getName()
    {
        if ($this->name === null) {
            $this->process();
        }
        return $this->name;
    }
    /**
     * @return string
     */
    public function getType()
    {
        if ($this->type === null) {
            $this->process();
        }
        return $this->type;
    }
    private function process()
    {
        $tokens = $this->tokenStream->tokens();
        if ($tokens[$this->id + 2] instanceof \MolliePrefix\PHP_Token_CONSTANT_ENCAPSED_STRING) {
            $this->name = \trim($tokens[$this->id + 2], "'\"");
            $this->type = \strtolower(\str_replace('PHP_Token_', '', \get_class($tokens[$this->id])));
        }
    }
}
\class_alias('MolliePrefix\\PHP_Token_Includes', 'PHP_Token_Includes', \false);
class PHP_Token_FUNCTION extends \MolliePrefix\PHP_TokenWithScopeAndVisibility
{
    /**
     * @var array
     */
    protected $arguments;
    /**
     * @var integer
     */
    protected $ccn;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $signature;
    /**
     * @return array
     */
    public function getArguments()
    {
        if ($this->arguments !== null) {
            return $this->arguments;
        }
        $this->arguments = array();
        $tokens = $this->tokenStream->tokens();
        $typeDeclaration = null;
        // Search for first token inside brackets
        $i = $this->id + 2;
        while (!$tokens[$i - 1] instanceof \MolliePrefix\PHP_Token_OPEN_BRACKET) {
            $i++;
        }
        while (!$tokens[$i] instanceof \MolliePrefix\PHP_Token_CLOSE_BRACKET) {
            if ($tokens[$i] instanceof \MolliePrefix\PHP_Token_STRING) {
                $typeDeclaration = (string) $tokens[$i];
            } elseif ($tokens[$i] instanceof \MolliePrefix\PHP_Token_VARIABLE) {
                $this->arguments[(string) $tokens[$i]] = $typeDeclaration;
                $typeDeclaration = null;
            }
            $i++;
        }
        return $this->arguments;
    }
    /**
     * @return string
     */
    public function getName()
    {
        if ($this->name !== null) {
            return $this->name;
        }
        $tokens = $this->tokenStream->tokens();
        for ($i = $this->id + 1; $i < \count($tokens); $i++) {
            if ($tokens[$i] instanceof \MolliePrefix\PHP_Token_STRING) {
                $this->name = (string) $tokens[$i];
                break;
            } elseif ($tokens[$i] instanceof \MolliePrefix\PHP_Token_AMPERSAND && $tokens[$i + 1] instanceof \MolliePrefix\PHP_Token_STRING) {
                $this->name = (string) $tokens[$i + 1];
                break;
            } elseif ($tokens[$i] instanceof \MolliePrefix\PHP_Token_OPEN_BRACKET) {
                $this->name = 'anonymous function';
                break;
            }
        }
        if ($this->name != 'anonymous function') {
            for ($i = $this->id; $i; --$i) {
                if ($tokens[$i] instanceof \MolliePrefix\PHP_Token_NAMESPACE) {
                    $this->name = $tokens[$i]->getName() . '\\' . $this->name;
                    break;
                }
                if ($tokens[$i] instanceof \MolliePrefix\PHP_Token_INTERFACE) {
                    break;
                }
            }
        }
        return $this->name;
    }
    /**
     * @return integer
     */
    public function getCCN()
    {
        if ($this->ccn !== null) {
            return $this->ccn;
        }
        $this->ccn = 1;
        $end = $this->getEndTokenId();
        $tokens = $this->tokenStream->tokens();
        for ($i = $this->id; $i <= $end; $i++) {
            switch (\get_class($tokens[$i])) {
                case 'PHP_Token_IF':
                case 'PHP_Token_ELSEIF':
                case 'PHP_Token_FOR':
                case 'PHP_Token_FOREACH':
                case 'PHP_Token_WHILE':
                case 'PHP_Token_CASE':
                case 'PHP_Token_CATCH':
                case 'PHP_Token_BOOLEAN_AND':
                case 'PHP_Token_LOGICAL_AND':
                case 'PHP_Token_BOOLEAN_OR':
                case 'PHP_Token_LOGICAL_OR':
                case 'PHP_Token_QUESTION_MARK':
                    $this->ccn++;
                    break;
            }
        }
        return $this->ccn;
    }
    /**
     * @return string
     */
    public function getSignature()
    {
        if ($this->signature !== null) {
            return $this->signature;
        }
        if ($this->getName() == 'anonymous function') {
            $this->signature = 'anonymous function';
            $i = $this->id + 1;
        } else {
            $this->signature = '';
            $i = $this->id + 2;
        }
        $tokens = $this->tokenStream->tokens();
        while (isset($tokens[$i]) && !$tokens[$i] instanceof \MolliePrefix\PHP_Token_OPEN_CURLY && !$tokens[$i] instanceof \MolliePrefix\PHP_Token_SEMICOLON) {
            $this->signature .= $tokens[$i++];
        }
        $this->signature = \trim($this->signature);
        return $this->signature;
    }
}
\class_alias('MolliePrefix\\PHP_Token_FUNCTION', 'PHP_Token_FUNCTION', \false);
class PHP_Token_INTERFACE extends \MolliePrefix\PHP_TokenWithScopeAndVisibility
{
    /**
     * @var array
     */
    protected $interfaces;
    /**
     * @return string
     */
    public function getName()
    {
        return (string) $this->tokenStream[$this->id + 2];
    }
    /**
     * @return boolean
     */
    public function hasParent()
    {
        return $this->tokenStream[$this->id + 4] instanceof \MolliePrefix\PHP_Token_EXTENDS;
    }
    /**
     * @return array
     */
    public function getPackage()
    {
        $className = $this->getName();
        $docComment = $this->getDocblock();
        $result = array('namespace' => '', 'fullPackage' => '', 'category' => '', 'package' => '', 'subpackage' => '');
        for ($i = $this->id; $i; --$i) {
            if ($this->tokenStream[$i] instanceof \MolliePrefix\PHP_Token_NAMESPACE) {
                $result['namespace'] = $this->tokenStream[$i]->getName();
                break;
            }
        }
        if (\preg_match('/@category[\\s]+([\\.\\w]+)/', $docComment, $matches)) {
            $result['category'] = $matches[1];
        }
        if (\preg_match('/@package[\\s]+([\\.\\w]+)/', $docComment, $matches)) {
            $result['package'] = $matches[1];
            $result['fullPackage'] = $matches[1];
        }
        if (\preg_match('/@subpackage[\\s]+([\\.\\w]+)/', $docComment, $matches)) {
            $result['subpackage'] = $matches[1];
            $result['fullPackage'] .= '.' . $matches[1];
        }
        if (empty($result['fullPackage'])) {
            $result['fullPackage'] = $this->arrayToName(\explode('_', \str_replace('\\', '_', $className)), '.');
        }
        return $result;
    }
    /**
     * @param  array  $parts
     * @param  string $join
     * @return string
     */
    protected function arrayToName(array $parts, $join = '\\')
    {
        $result = '';
        if (\count($parts) > 1) {
            \array_pop($parts);
            $result = \join($join, $parts);
        }
        return $result;
    }
    /**
     * @return boolean|string
     */
    public function getParent()
    {
        if (!$this->hasParent()) {
            return \false;
        }
        $i = $this->id + 6;
        $tokens = $this->tokenStream->tokens();
        $className = (string) $tokens[$i];
        while (isset($tokens[$i + 1]) && !$tokens[$i + 1] instanceof \MolliePrefix\PHP_Token_WHITESPACE) {
            $className .= (string) $tokens[++$i];
        }
        return $className;
    }
    /**
     * @return boolean
     */
    public function hasInterfaces()
    {
        return isset($this->tokenStream[$this->id + 4]) && $this->tokenStream[$this->id + 4] instanceof \MolliePrefix\PHP_Token_IMPLEMENTS || isset($this->tokenStream[$this->id + 8]) && $this->tokenStream[$this->id + 8] instanceof \MolliePrefix\PHP_Token_IMPLEMENTS;
    }
    /**
     * @return array|boolean
     */
    public function getInterfaces()
    {
        if ($this->interfaces !== null) {
            return $this->interfaces;
        }
        if (!$this->hasInterfaces()) {
            return $this->interfaces = \false;
        }
        if ($this->tokenStream[$this->id + 4] instanceof \MolliePrefix\PHP_Token_IMPLEMENTS) {
            $i = $this->id + 3;
        } else {
            $i = $this->id + 7;
        }
        $tokens = $this->tokenStream->tokens();
        while (!$tokens[$i + 1] instanceof \MolliePrefix\PHP_Token_OPEN_CURLY) {
            $i++;
            if ($tokens[$i] instanceof \MolliePrefix\PHP_Token_STRING) {
                $this->interfaces[] = (string) $tokens[$i];
            }
        }
        return $this->interfaces;
    }
}
\class_alias('MolliePrefix\\PHP_Token_INTERFACE', 'PHP_Token_INTERFACE', \false);
class PHP_Token_ABSTRACT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ABSTRACT', 'PHP_Token_ABSTRACT', \false);
class PHP_Token_AMPERSAND extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_AMPERSAND', 'PHP_Token_AMPERSAND', \false);
class PHP_Token_AND_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_AND_EQUAL', 'PHP_Token_AND_EQUAL', \false);
class PHP_Token_ARRAY extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ARRAY', 'PHP_Token_ARRAY', \false);
class PHP_Token_ARRAY_CAST extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ARRAY_CAST', 'PHP_Token_ARRAY_CAST', \false);
class PHP_Token_AS extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_AS', 'PHP_Token_AS', \false);
class PHP_Token_AT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_AT', 'PHP_Token_AT', \false);
class PHP_Token_BACKTICK extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_BACKTICK', 'PHP_Token_BACKTICK', \false);
class PHP_Token_BAD_CHARACTER extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_BAD_CHARACTER', 'PHP_Token_BAD_CHARACTER', \false);
class PHP_Token_BOOLEAN_AND extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_BOOLEAN_AND', 'PHP_Token_BOOLEAN_AND', \false);
class PHP_Token_BOOLEAN_OR extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_BOOLEAN_OR', 'PHP_Token_BOOLEAN_OR', \false);
class PHP_Token_BOOL_CAST extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_BOOL_CAST', 'PHP_Token_BOOL_CAST', \false);
class PHP_Token_BREAK extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_BREAK', 'PHP_Token_BREAK', \false);
class PHP_Token_CARET extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CARET', 'PHP_Token_CARET', \false);
class PHP_Token_CASE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CASE', 'PHP_Token_CASE', \false);
class PHP_Token_CATCH extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CATCH', 'PHP_Token_CATCH', \false);
class PHP_Token_CHARACTER extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CHARACTER', 'PHP_Token_CHARACTER', \false);
class PHP_Token_CLASS extends \MolliePrefix\PHP_Token_INTERFACE
{
    /**
     * @return string
     */
    public function getName()
    {
        $next = $this->tokenStream[$this->id + 1];
        if ($next instanceof \MolliePrefix\PHP_Token_WHITESPACE) {
            $next = $this->tokenStream[$this->id + 2];
        }
        if ($next instanceof \MolliePrefix\PHP_Token_STRING) {
            return (string) $next;
        }
        if ($next instanceof \MolliePrefix\PHP_Token_OPEN_CURLY || $next instanceof \MolliePrefix\PHP_Token_EXTENDS || $next instanceof \MolliePrefix\PHP_Token_IMPLEMENTS) {
            return 'anonymous class';
        }
    }
}
\class_alias('MolliePrefix\\PHP_Token_CLASS', 'PHP_Token_CLASS', \false);
class PHP_Token_CLASS_C extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CLASS_C', 'PHP_Token_CLASS_C', \false);
class PHP_Token_CLASS_NAME_CONSTANT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CLASS_NAME_CONSTANT', 'PHP_Token_CLASS_NAME_CONSTANT', \false);
class PHP_Token_CLONE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CLONE', 'PHP_Token_CLONE', \false);
class PHP_Token_CLOSE_BRACKET extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CLOSE_BRACKET', 'PHP_Token_CLOSE_BRACKET', \false);
class PHP_Token_CLOSE_CURLY extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CLOSE_CURLY', 'PHP_Token_CLOSE_CURLY', \false);
class PHP_Token_CLOSE_SQUARE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CLOSE_SQUARE', 'PHP_Token_CLOSE_SQUARE', \false);
class PHP_Token_CLOSE_TAG extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CLOSE_TAG', 'PHP_Token_CLOSE_TAG', \false);
class PHP_Token_COLON extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_COLON', 'PHP_Token_COLON', \false);
class PHP_Token_COMMA extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_COMMA', 'PHP_Token_COMMA', \false);
class PHP_Token_COMMENT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_COMMENT', 'PHP_Token_COMMENT', \false);
class PHP_Token_CONCAT_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CONCAT_EQUAL', 'PHP_Token_CONCAT_EQUAL', \false);
class PHP_Token_CONST extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CONST', 'PHP_Token_CONST', \false);
class PHP_Token_CONSTANT_ENCAPSED_STRING extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CONSTANT_ENCAPSED_STRING', 'PHP_Token_CONSTANT_ENCAPSED_STRING', \false);
class PHP_Token_CONTINUE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CONTINUE', 'PHP_Token_CONTINUE', \false);
class PHP_Token_CURLY_OPEN extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_CURLY_OPEN', 'PHP_Token_CURLY_OPEN', \false);
class PHP_Token_DEC extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DEC', 'PHP_Token_DEC', \false);
class PHP_Token_DECLARE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DECLARE', 'PHP_Token_DECLARE', \false);
class PHP_Token_DEFAULT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DEFAULT', 'PHP_Token_DEFAULT', \false);
class PHP_Token_DIV extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DIV', 'PHP_Token_DIV', \false);
class PHP_Token_DIV_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DIV_EQUAL', 'PHP_Token_DIV_EQUAL', \false);
class PHP_Token_DNUMBER extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DNUMBER', 'PHP_Token_DNUMBER', \false);
class PHP_Token_DO extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DO', 'PHP_Token_DO', \false);
class PHP_Token_DOC_COMMENT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DOC_COMMENT', 'PHP_Token_DOC_COMMENT', \false);
class PHP_Token_DOLLAR extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DOLLAR', 'PHP_Token_DOLLAR', \false);
class PHP_Token_DOLLAR_OPEN_CURLY_BRACES extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DOLLAR_OPEN_CURLY_BRACES', 'PHP_Token_DOLLAR_OPEN_CURLY_BRACES', \false);
class PHP_Token_DOT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DOT', 'PHP_Token_DOT', \false);
class PHP_Token_DOUBLE_ARROW extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DOUBLE_ARROW', 'PHP_Token_DOUBLE_ARROW', \false);
class PHP_Token_DOUBLE_CAST extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DOUBLE_CAST', 'PHP_Token_DOUBLE_CAST', \false);
class PHP_Token_DOUBLE_COLON extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DOUBLE_COLON', 'PHP_Token_DOUBLE_COLON', \false);
class PHP_Token_DOUBLE_QUOTES extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_DOUBLE_QUOTES', 'PHP_Token_DOUBLE_QUOTES', \false);
class PHP_Token_ECHO extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ECHO', 'PHP_Token_ECHO', \false);
class PHP_Token_ELSE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ELSE', 'PHP_Token_ELSE', \false);
class PHP_Token_ELSEIF extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ELSEIF', 'PHP_Token_ELSEIF', \false);
class PHP_Token_EMPTY extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_EMPTY', 'PHP_Token_EMPTY', \false);
class PHP_Token_ENCAPSED_AND_WHITESPACE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ENCAPSED_AND_WHITESPACE', 'PHP_Token_ENCAPSED_AND_WHITESPACE', \false);
class PHP_Token_ENDDECLARE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ENDDECLARE', 'PHP_Token_ENDDECLARE', \false);
class PHP_Token_ENDFOR extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ENDFOR', 'PHP_Token_ENDFOR', \false);
class PHP_Token_ENDFOREACH extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ENDFOREACH', 'PHP_Token_ENDFOREACH', \false);
class PHP_Token_ENDIF extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ENDIF', 'PHP_Token_ENDIF', \false);
class PHP_Token_ENDSWITCH extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ENDSWITCH', 'PHP_Token_ENDSWITCH', \false);
class PHP_Token_ENDWHILE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ENDWHILE', 'PHP_Token_ENDWHILE', \false);
class PHP_Token_END_HEREDOC extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_END_HEREDOC', 'PHP_Token_END_HEREDOC', \false);
class PHP_Token_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_EQUAL', 'PHP_Token_EQUAL', \false);
class PHP_Token_EVAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_EVAL', 'PHP_Token_EVAL', \false);
class PHP_Token_EXCLAMATION_MARK extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_EXCLAMATION_MARK', 'PHP_Token_EXCLAMATION_MARK', \false);
class PHP_Token_EXIT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_EXIT', 'PHP_Token_EXIT', \false);
class PHP_Token_EXTENDS extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_EXTENDS', 'PHP_Token_EXTENDS', \false);
class PHP_Token_FILE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_FILE', 'PHP_Token_FILE', \false);
class PHP_Token_FINAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_FINAL', 'PHP_Token_FINAL', \false);
class PHP_Token_FOR extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_FOR', 'PHP_Token_FOR', \false);
class PHP_Token_FOREACH extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_FOREACH', 'PHP_Token_FOREACH', \false);
class PHP_Token_FUNC_C extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_FUNC_C', 'PHP_Token_FUNC_C', \false);
class PHP_Token_GLOBAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_GLOBAL', 'PHP_Token_GLOBAL', \false);
class PHP_Token_GT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_GT', 'PHP_Token_GT', \false);
class PHP_Token_IF extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_IF', 'PHP_Token_IF', \false);
class PHP_Token_IMPLEMENTS extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_IMPLEMENTS', 'PHP_Token_IMPLEMENTS', \false);
class PHP_Token_INC extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_INC', 'PHP_Token_INC', \false);
class PHP_Token_INCLUDE extends \MolliePrefix\PHP_Token_Includes
{
}
\class_alias('MolliePrefix\\PHP_Token_INCLUDE', 'PHP_Token_INCLUDE', \false);
class PHP_Token_INCLUDE_ONCE extends \MolliePrefix\PHP_Token_Includes
{
}
\class_alias('MolliePrefix\\PHP_Token_INCLUDE_ONCE', 'PHP_Token_INCLUDE_ONCE', \false);
class PHP_Token_INLINE_HTML extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_INLINE_HTML', 'PHP_Token_INLINE_HTML', \false);
class PHP_Token_INSTANCEOF extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_INSTANCEOF', 'PHP_Token_INSTANCEOF', \false);
class PHP_Token_INT_CAST extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_INT_CAST', 'PHP_Token_INT_CAST', \false);
class PHP_Token_ISSET extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ISSET', 'PHP_Token_ISSET', \false);
class PHP_Token_IS_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_IS_EQUAL', 'PHP_Token_IS_EQUAL', \false);
class PHP_Token_IS_GREATER_OR_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_IS_GREATER_OR_EQUAL', 'PHP_Token_IS_GREATER_OR_EQUAL', \false);
class PHP_Token_IS_IDENTICAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_IS_IDENTICAL', 'PHP_Token_IS_IDENTICAL', \false);
class PHP_Token_IS_NOT_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_IS_NOT_EQUAL', 'PHP_Token_IS_NOT_EQUAL', \false);
class PHP_Token_IS_NOT_IDENTICAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_IS_NOT_IDENTICAL', 'PHP_Token_IS_NOT_IDENTICAL', \false);
class PHP_Token_IS_SMALLER_OR_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_IS_SMALLER_OR_EQUAL', 'PHP_Token_IS_SMALLER_OR_EQUAL', \false);
class PHP_Token_LINE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_LINE', 'PHP_Token_LINE', \false);
class PHP_Token_LIST extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_LIST', 'PHP_Token_LIST', \false);
class PHP_Token_LNUMBER extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_LNUMBER', 'PHP_Token_LNUMBER', \false);
class PHP_Token_LOGICAL_AND extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_LOGICAL_AND', 'PHP_Token_LOGICAL_AND', \false);
class PHP_Token_LOGICAL_OR extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_LOGICAL_OR', 'PHP_Token_LOGICAL_OR', \false);
class PHP_Token_LOGICAL_XOR extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_LOGICAL_XOR', 'PHP_Token_LOGICAL_XOR', \false);
class PHP_Token_LT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_LT', 'PHP_Token_LT', \false);
class PHP_Token_METHOD_C extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_METHOD_C', 'PHP_Token_METHOD_C', \false);
class PHP_Token_MINUS extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_MINUS', 'PHP_Token_MINUS', \false);
class PHP_Token_MINUS_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_MINUS_EQUAL', 'PHP_Token_MINUS_EQUAL', \false);
class PHP_Token_MOD_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_MOD_EQUAL', 'PHP_Token_MOD_EQUAL', \false);
class PHP_Token_MULT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_MULT', 'PHP_Token_MULT', \false);
class PHP_Token_MUL_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_MUL_EQUAL', 'PHP_Token_MUL_EQUAL', \false);
class PHP_Token_NEW extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_NEW', 'PHP_Token_NEW', \false);
class PHP_Token_NUM_STRING extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_NUM_STRING', 'PHP_Token_NUM_STRING', \false);
class PHP_Token_OBJECT_CAST extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_OBJECT_CAST', 'PHP_Token_OBJECT_CAST', \false);
class PHP_Token_OBJECT_OPERATOR extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_OBJECT_OPERATOR', 'PHP_Token_OBJECT_OPERATOR', \false);
class PHP_Token_OPEN_BRACKET extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_OPEN_BRACKET', 'PHP_Token_OPEN_BRACKET', \false);
class PHP_Token_OPEN_CURLY extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_OPEN_CURLY', 'PHP_Token_OPEN_CURLY', \false);
class PHP_Token_OPEN_SQUARE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_OPEN_SQUARE', 'PHP_Token_OPEN_SQUARE', \false);
class PHP_Token_OPEN_TAG extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_OPEN_TAG', 'PHP_Token_OPEN_TAG', \false);
class PHP_Token_OPEN_TAG_WITH_ECHO extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_OPEN_TAG_WITH_ECHO', 'PHP_Token_OPEN_TAG_WITH_ECHO', \false);
class PHP_Token_OR_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_OR_EQUAL', 'PHP_Token_OR_EQUAL', \false);
class PHP_Token_PAAMAYIM_NEKUDOTAYIM extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_PAAMAYIM_NEKUDOTAYIM', 'PHP_Token_PAAMAYIM_NEKUDOTAYIM', \false);
class PHP_Token_PERCENT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_PERCENT', 'PHP_Token_PERCENT', \false);
class PHP_Token_PIPE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_PIPE', 'PHP_Token_PIPE', \false);
class PHP_Token_PLUS extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_PLUS', 'PHP_Token_PLUS', \false);
class PHP_Token_PLUS_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_PLUS_EQUAL', 'PHP_Token_PLUS_EQUAL', \false);
class PHP_Token_PRINT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_PRINT', 'PHP_Token_PRINT', \false);
class PHP_Token_PRIVATE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_PRIVATE', 'PHP_Token_PRIVATE', \false);
class PHP_Token_PROTECTED extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_PROTECTED', 'PHP_Token_PROTECTED', \false);
class PHP_Token_PUBLIC extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_PUBLIC', 'PHP_Token_PUBLIC', \false);
class PHP_Token_QUESTION_MARK extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_QUESTION_MARK', 'PHP_Token_QUESTION_MARK', \false);
class PHP_Token_REQUIRE extends \MolliePrefix\PHP_Token_Includes
{
}
\class_alias('MolliePrefix\\PHP_Token_REQUIRE', 'PHP_Token_REQUIRE', \false);
class PHP_Token_REQUIRE_ONCE extends \MolliePrefix\PHP_Token_Includes
{
}
\class_alias('MolliePrefix\\PHP_Token_REQUIRE_ONCE', 'PHP_Token_REQUIRE_ONCE', \false);
class PHP_Token_RETURN extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_RETURN', 'PHP_Token_RETURN', \false);
class PHP_Token_SEMICOLON extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_SEMICOLON', 'PHP_Token_SEMICOLON', \false);
class PHP_Token_SL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_SL', 'PHP_Token_SL', \false);
class PHP_Token_SL_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_SL_EQUAL', 'PHP_Token_SL_EQUAL', \false);
class PHP_Token_SR extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_SR', 'PHP_Token_SR', \false);
class PHP_Token_SR_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_SR_EQUAL', 'PHP_Token_SR_EQUAL', \false);
class PHP_Token_START_HEREDOC extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_START_HEREDOC', 'PHP_Token_START_HEREDOC', \false);
class PHP_Token_STATIC extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_STATIC', 'PHP_Token_STATIC', \false);
class PHP_Token_STRING extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_STRING', 'PHP_Token_STRING', \false);
class PHP_Token_STRING_CAST extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_STRING_CAST', 'PHP_Token_STRING_CAST', \false);
class PHP_Token_STRING_VARNAME extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_STRING_VARNAME', 'PHP_Token_STRING_VARNAME', \false);
class PHP_Token_SWITCH extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_SWITCH', 'PHP_Token_SWITCH', \false);
class PHP_Token_THROW extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_THROW', 'PHP_Token_THROW', \false);
class PHP_Token_TILDE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_TILDE', 'PHP_Token_TILDE', \false);
class PHP_Token_TRY extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_TRY', 'PHP_Token_TRY', \false);
class PHP_Token_UNSET extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_UNSET', 'PHP_Token_UNSET', \false);
class PHP_Token_UNSET_CAST extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_UNSET_CAST', 'PHP_Token_UNSET_CAST', \false);
class PHP_Token_USE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_USE', 'PHP_Token_USE', \false);
class PHP_Token_USE_FUNCTION extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_USE_FUNCTION', 'PHP_Token_USE_FUNCTION', \false);
class PHP_Token_VAR extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_VAR', 'PHP_Token_VAR', \false);
class PHP_Token_VARIABLE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_VARIABLE', 'PHP_Token_VARIABLE', \false);
class PHP_Token_WHILE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_WHILE', 'PHP_Token_WHILE', \false);
class PHP_Token_WHITESPACE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_WHITESPACE', 'PHP_Token_WHITESPACE', \false);
class PHP_Token_XOR_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_XOR_EQUAL', 'PHP_Token_XOR_EQUAL', \false);
// Tokens introduced in PHP 5.1
class PHP_Token_HALT_COMPILER extends \MolliePrefix\PHP_Token
{
}
// Tokens introduced in PHP 5.1
\class_alias('MolliePrefix\\PHP_Token_HALT_COMPILER', 'PHP_Token_HALT_COMPILER', \false);
// Tokens introduced in PHP 5.3
class PHP_Token_DIR extends \MolliePrefix\PHP_Token
{
}
// Tokens introduced in PHP 5.3
\class_alias('MolliePrefix\\PHP_Token_DIR', 'PHP_Token_DIR', \false);
class PHP_Token_GOTO extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_GOTO', 'PHP_Token_GOTO', \false);
class PHP_Token_NAMESPACE extends \MolliePrefix\PHP_TokenWithScope
{
    /**
     * @return string
     */
    public function getName()
    {
        $tokens = $this->tokenStream->tokens();
        $namespace = (string) $tokens[$this->id + 2];
        for ($i = $this->id + 3;; $i += 2) {
            if (isset($tokens[$i]) && $tokens[$i] instanceof \MolliePrefix\PHP_Token_NS_SEPARATOR) {
                $namespace .= '\\' . $tokens[$i + 1];
            } else {
                break;
            }
        }
        return $namespace;
    }
}
\class_alias('MolliePrefix\\PHP_Token_NAMESPACE', 'PHP_Token_NAMESPACE', \false);
class PHP_Token_NS_C extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_NS_C', 'PHP_Token_NS_C', \false);
class PHP_Token_NS_SEPARATOR extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_NS_SEPARATOR', 'PHP_Token_NS_SEPARATOR', \false);
// Tokens introduced in PHP 5.4
class PHP_Token_CALLABLE extends \MolliePrefix\PHP_Token
{
}
// Tokens introduced in PHP 5.4
\class_alias('MolliePrefix\\PHP_Token_CALLABLE', 'PHP_Token_CALLABLE', \false);
class PHP_Token_INSTEADOF extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_INSTEADOF', 'PHP_Token_INSTEADOF', \false);
class PHP_Token_TRAIT extends \MolliePrefix\PHP_Token_INTERFACE
{
}
\class_alias('MolliePrefix\\PHP_Token_TRAIT', 'PHP_Token_TRAIT', \false);
class PHP_Token_TRAIT_C extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_TRAIT_C', 'PHP_Token_TRAIT_C', \false);
// Tokens introduced in PHP 5.5
class PHP_Token_FINALLY extends \MolliePrefix\PHP_Token
{
}
// Tokens introduced in PHP 5.5
\class_alias('MolliePrefix\\PHP_Token_FINALLY', 'PHP_Token_FINALLY', \false);
class PHP_Token_YIELD extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_YIELD', 'PHP_Token_YIELD', \false);
// Tokens introduced in PHP 5.6
class PHP_Token_ELLIPSIS extends \MolliePrefix\PHP_Token
{
}
// Tokens introduced in PHP 5.6
\class_alias('MolliePrefix\\PHP_Token_ELLIPSIS', 'PHP_Token_ELLIPSIS', \false);
class PHP_Token_POW extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_POW', 'PHP_Token_POW', \false);
class PHP_Token_POW_EQUAL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_POW_EQUAL', 'PHP_Token_POW_EQUAL', \false);
// Tokens introduced in PHP 7.0
class PHP_Token_COALESCE extends \MolliePrefix\PHP_Token
{
}
// Tokens introduced in PHP 7.0
\class_alias('MolliePrefix\\PHP_Token_COALESCE', 'PHP_Token_COALESCE', \false);
class PHP_Token_SPACESHIP extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_SPACESHIP', 'PHP_Token_SPACESHIP', \false);
class PHP_Token_YIELD_FROM extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_YIELD_FROM', 'PHP_Token_YIELD_FROM', \false);
// Tokens introduced in HackLang / HHVM
class PHP_Token_ASYNC extends \MolliePrefix\PHP_Token
{
}
// Tokens introduced in HackLang / HHVM
\class_alias('MolliePrefix\\PHP_Token_ASYNC', 'PHP_Token_ASYNC', \false);
class PHP_Token_AWAIT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_AWAIT', 'PHP_Token_AWAIT', \false);
class PHP_Token_COMPILER_HALT_OFFSET extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_COMPILER_HALT_OFFSET', 'PHP_Token_COMPILER_HALT_OFFSET', \false);
class PHP_Token_ENUM extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ENUM', 'PHP_Token_ENUM', \false);
class PHP_Token_EQUALS extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_EQUALS', 'PHP_Token_EQUALS', \false);
class PHP_Token_IN extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_IN', 'PHP_Token_IN', \false);
class PHP_Token_JOIN extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_JOIN', 'PHP_Token_JOIN', \false);
class PHP_Token_LAMBDA_ARROW extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_LAMBDA_ARROW', 'PHP_Token_LAMBDA_ARROW', \false);
class PHP_Token_LAMBDA_CP extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_LAMBDA_CP', 'PHP_Token_LAMBDA_CP', \false);
class PHP_Token_LAMBDA_OP extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_LAMBDA_OP', 'PHP_Token_LAMBDA_OP', \false);
class PHP_Token_ONUMBER extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_ONUMBER', 'PHP_Token_ONUMBER', \false);
class PHP_Token_NULLSAFE_OBJECT_OPERATOR extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_NULLSAFE_OBJECT_OPERATOR', 'PHP_Token_NULLSAFE_OBJECT_OPERATOR', \false);
class PHP_Token_SHAPE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_SHAPE', 'PHP_Token_SHAPE', \false);
class PHP_Token_SUPER extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_SUPER', 'PHP_Token_SUPER', \false);
class PHP_Token_TYPE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_TYPE', 'PHP_Token_TYPE', \false);
class PHP_Token_TYPELIST_GT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_TYPELIST_GT', 'PHP_Token_TYPELIST_GT', \false);
class PHP_Token_TYPELIST_LT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_TYPELIST_LT', 'PHP_Token_TYPELIST_LT', \false);
class PHP_Token_WHERE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_WHERE', 'PHP_Token_WHERE', \false);
class PHP_Token_XHP_ATTRIBUTE extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_XHP_ATTRIBUTE', 'PHP_Token_XHP_ATTRIBUTE', \false);
class PHP_Token_XHP_CATEGORY extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_XHP_CATEGORY', 'PHP_Token_XHP_CATEGORY', \false);
class PHP_Token_XHP_CATEGORY_LABEL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_XHP_CATEGORY_LABEL', 'PHP_Token_XHP_CATEGORY_LABEL', \false);
class PHP_Token_XHP_CHILDREN extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_XHP_CHILDREN', 'PHP_Token_XHP_CHILDREN', \false);
class PHP_Token_XHP_LABEL extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_XHP_LABEL', 'PHP_Token_XHP_LABEL', \false);
class PHP_Token_XHP_REQUIRED extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_XHP_REQUIRED', 'PHP_Token_XHP_REQUIRED', \false);
class PHP_Token_XHP_TAG_GT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_XHP_TAG_GT', 'PHP_Token_XHP_TAG_GT', \false);
class PHP_Token_XHP_TAG_LT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_XHP_TAG_LT', 'PHP_Token_XHP_TAG_LT', \false);
class PHP_Token_XHP_TEXT extends \MolliePrefix\PHP_Token
{
}
\class_alias('MolliePrefix\\PHP_Token_XHP_TEXT', 'PHP_Token_XHP_TEXT', \false);
