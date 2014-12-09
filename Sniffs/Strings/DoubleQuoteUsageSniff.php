<?php
/**
 * ONGR_Sniffs_Strings_DoubleQuoteUsageSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

namespace ONGR\Sniffs\Strings;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * ONGR_Sniffs_Strings_DoubleQuoteUsageSniff.
 *
 * Makes sure that any use of Double Quotes ("") are warranted.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class DoubleQuoteUsageSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_CONSTANT_ENCAPSED_STRING,
            T_DOUBLE_QUOTED_STRING,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // We are only interested in the first token in a multi-line string.
        if ($tokens[$stackPtr]['code'] === $tokens[($stackPtr - 1)]['code']) {
            return;
        }

        $workingString = $tokens[$stackPtr]['content'];
        $i = ($stackPtr + 1);
        while ($tokens[$i]['code'] === $tokens[$stackPtr]['code']) {
            $workingString .= $tokens[$i]['content'];
            $i++;
        }

        // Check if it's a double quoted string.
        if (strpos($workingString, '"') === false) {
            return;
        }

        // Make sure it's not a part of a string started in a previous line.
        // If it is, then we have already checked it.
        if ($workingString[0] !== '"') {
            return;
        }

        // Work through the following tokens, in case this string is stretched
        // over multiple Lines.
        for ($i = ($stackPtr + 1); $i < $phpcsFile->numTokens; $i++) {
            if ($tokens[$i]['type'] !== 'T_CONSTANT_ENCAPSED_STRING') {
                break;
            }

            $workingString .= $tokens[$i]['content'];
        }

        $allowedChars = [
            '\0',
            '\n',
            '\r',
            '\f',
            '\t',
            '\v',
            '\x',
            '\b',
            '\'',
            '$',
        ];

        foreach ($allowedChars as $testChar) {
            if (strpos($workingString, $testChar) !== false) {
                return;
            }
        }

        $error = 'String %s does not require double quotes; use single quotes instead';
        $data = [$workingString];
        $phpcsFile->addError($error, $stackPtr, 'NotRequired', $data);
    }
}
