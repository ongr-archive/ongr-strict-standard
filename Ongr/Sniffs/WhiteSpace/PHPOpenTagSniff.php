<?php
/**
 * Ongr_Sniffs_WhiteSpace_PHPOpenTagSniff
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Ongr_Strict_Codin_Standard
 * @author   Ongr Team <info@nfq.com>
 * @license  http://spdx.org/licenses/MIT MIT License
 * @link     https://github.com/ongr-io/Ongr
 */

/**
 * Ongr_Sniffs_WhiteSpace_PHPOpenTagSniff
 *
 * Ensures a blank line after php open tag.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Ongr_Strict_Codin_Standard
 * @author   Ongr Team <info@nfq.com>
 * @license  http://spdx.org/licenses/MIT MIT License
 * @version  Release: @package_version@
 * @link     https://github.com/ongr-io/Ongr
 */
class Ongr_Sniffs_WhiteSpace_PHPOpenTagSniff implements PHP_CodeSniffer_Sniff
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
        #TODO add an autofix.
        $tokens = $phpcsFile->getTokens();
        if (isset($tokens[$stackPtr + 1]) && $tokens[$stackPtr + 1]['code'] !== T_WHITESPACE) {
            $error = 'There must be one blank line after the php open tag and no whitespaces';
            $phpcsFile->addError($error, $stackPtr, 'BlankLineAfter');
        }
        $content = $phpcsFile->findNext(T_OPEN_TAG, $stackPtr) + 1;
        while ($tokens[$content]['code'] == T_WHITESPACE) {
            ++$content;
        }
        $spaces = ($tokens[$content]['line'] - $tokens[$stackPtr]['line']) - 1;
        if ($spaces > 1) {
            $error = 'There must be only 1 blank line after the php open tag; Found ' . $spaces;
            $phpcsFile->addError($error, $stackPtr, 'BlankLinesAfter');
        }
    }
}
