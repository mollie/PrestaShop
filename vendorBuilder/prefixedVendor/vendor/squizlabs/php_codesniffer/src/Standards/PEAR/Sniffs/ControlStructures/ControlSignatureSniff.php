<?php

/**
 * Verifies that control statements conform to their coding standards.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2015 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */
namespace MolliePrefix\PHP_CodeSniffer\Standards\PEAR\Sniffs\ControlStructures;

use MolliePrefix\PHP_CodeSniffer\Sniffs\AbstractPatternSniff;
class ControlSignatureSniff extends \MolliePrefix\PHP_CodeSniffer\Sniffs\AbstractPatternSniff
{
    /**
     * If true, comments will be ignored if they are found in the code.
     *
     * @var boolean
     */
    public $ignoreComments = \true;
    /**
     * Returns the patterns that this test wishes to verify.
     *
     * @return string[]
     */
    protected function getPatterns()
    {
        return ['do {EOL...} while (...);EOL', 'while (...) {EOL', 'for (...) {EOL', 'if (...) {EOL', 'foreach (...) {EOL', '} else if (...) {EOL', '} elseif (...) {EOL', '} else {EOL', 'do {EOL'];
    }
    //end getPatterns()
}
//end class
