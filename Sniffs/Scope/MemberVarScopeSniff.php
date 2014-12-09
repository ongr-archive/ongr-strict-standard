<?php
/**
 * Verifies that class members have scope modifiers.
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

namespace ONGR\Sniffs\Scope;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Standards_AbstractVariableSniff;
use PHP_CodeSniffer_Tokens;

/**
 * Verifies that class members have scope modifiers.
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
class MemberVarScopeSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff
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
        $modifier = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$scopeModifiers, $stackPtr);

        if (($modifier === false) || ($tokens[$modifier]['line'] !== $tokens[$stackPtr]['line'])) {
            $error = 'Scope modifier not specified for member variable "%s"';
            $data = [$tokens[$stackPtr]['content']];
            $phpcsFile->addError($error, $stackPtr, 'Missing', $data);
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
