<?php

/**
 * ONGR_Sniffs_WhiteSpace_PHPOpenTagSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    ONGR Team <info@ongr.io>
 * @copyright 2015 NFQ Technologies UAB
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace ONGR\Sniffs\WhiteSpace;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Ensures a blank line after php open tag.
 */
class PHPOpenTagSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr + 1]) && $tokens[$stackPtr + 1]['code'] !== T_WHITESPACE) {
            $error = 'There must be one blank line after the php open tag and no whitespaces';
            $phpcsFile->addError($error, $stackPtr, 'BlankLineAfter');
        }
    }
}
