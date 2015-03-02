<?php
/**
 * Ongr_Sniffs_WhiteSpace_NamespaceSpacingSniff
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
 * Ongr_Sniffs_WhiteSpace_NamespaceSpacingSniff
 *
 * Class to validate right amount of whitespaces after namespace declaration.
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
class Ongr_Sniffs_WhiteSpace_NamespaceSpacingSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_NAMESPACE];
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
        #TODO add an autofix.
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_opener'])) {
            // Skip bracketed namespaces.
            return;
        }
        $content = $phpcsFile->findNext(T_SEMICOLON, $stackPtr) + 1;
        while ($tokens[$content]['code'] == T_WHITESPACE) {
            ++$content;
        }
        if (($tokens[$content]['line'] - $tokens[$stackPtr]['line']) > 2) {
            // Case for minimum lines is written somewhere else.
            $phpcsFile->addError(
                'There must be exactly 1 blank line after namespace declaration',
                $stackPtr,
                'NamespaceSpacing'
            );
        }

        $content = $phpcsFile->findNext(T_NAMESPACE, $stackPtr) - 1;
        while ($tokens[$content]['code'] == T_WHITESPACE) {
            --$content;
        }
        $spaces = ($tokens[$content]['line'] - $tokens[$stackPtr]['line']) * -1;
        $spaces--;
        if ($spaces > 2) {
            // Case for minimum lines is written somewhere else.
            $phpcsFile->addError(
                'There must be exactly 1 blank line before namespace declaration; Found ' . $spaces,
                $stackPtr,
                'NamespaceSpacing'
            );
        }
    }
}
