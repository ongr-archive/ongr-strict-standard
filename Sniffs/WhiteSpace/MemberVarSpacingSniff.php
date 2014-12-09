<?php
/**
 * Verifies that class members are spaced correctly.
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

namespace ONGR\Sniffs\WhiteSpace;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Standards_AbstractVariableSniff;
use PHP_CodeSniffer_Tokens;

/**
 * Verifies that class members are spaced correctly.
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
class MemberVarSpacingSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff
{
    /**
     * Processes the function tokens within the class.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // There needs to be 1 blank line before the var, not counting comments.
        $prevLineToken = null;
        for ($i = ($stackPtr - 1); $i > 0; $i--) {
            if (in_array($tokens[$i]['code'], PHP_CodeSniffer_Tokens::$commentTokens) === true) {
                // Skip comments.
                continue;
            } elseif (strpos($tokens[$i]['content'], $phpcsFile->eolChar) === false) {
                // Not the end of the line.
                continue;
            } else {
                // If this is a WHITESPACE token, and the token right before
                // it is a DOC_COMMENT, then it is just the newline after the
                // member var's comment, and can be skipped.
                if ($tokens[$i]['code'] === T_WHITESPACE
                    && in_array($tokens[($i - 1)]['code'], PHP_CodeSniffer_Tokens::$commentTokens) === true
                ) {
                    continue;
                }

                $prevLineToken = $i;
                break;
            }
        }

        if ($prevLineToken === null) {
            // Never found the previous line, which means
            // there are 0 blank lines before the member var.
            $foundLines = 0;
        } else {
            $prevContent = $phpcsFile->findPrevious(
                [T_WHITESPACE, T_DOC_COMMENT, T_OPEN_CURLY_BRACKET],
                $prevLineToken,
                null,
                true
            );
            $foundLines = ($tokens[$prevLineToken]['line'] - $tokens[$prevContent]['line']);
        }

        if ($foundLines !== 1) {
            $error = 'Expected 1 blank line before member var; %s found';
            $data = [$foundLines];
            $phpcsFile->addError($error, $stackPtr, 'Incorrect', $data);
        }
    }

    /**
     * Processes normal variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
    }

    /**
     * Processes variables in double quoted strings.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
    }
}
