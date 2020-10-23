<?php

/**
 * Ensures that boolean operators are only used inside control structure conditions.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2015 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */
namespace MolliePrefix\PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP;

use MolliePrefix\PHP_CodeSniffer\Files\File;
use MolliePrefix\PHP_CodeSniffer\Sniffs\Sniff;
use MolliePrefix\PHP_CodeSniffer\Util\Tokens;
class DisallowBooleanStatementSniff implements \MolliePrefix\PHP_CodeSniffer\Sniffs\Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return \MolliePrefix\PHP_CodeSniffer\Util\Tokens::$booleanOperators;
    }
    //end register()
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(\MolliePrefix\PHP_CodeSniffer\Files\File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (isset($tokens[$stackPtr]['nested_parenthesis']) === \true) {
            foreach ($tokens[$stackPtr]['nested_parenthesis'] as $open => $close) {
                if (isset($tokens[$open]['parenthesis_owner']) === \true) {
                    // Any owner means we are not just a simple statement.
                    return;
                }
            }
        }
        $error = 'Boolean operators are not allowed outside of control structure conditions';
        $phpcsFile->addError($error, $stackPtr, 'Found');
    }
    //end process()
}
//end class
