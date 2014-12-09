<?php
/**
 * ONGR_Sniffs_CSS_DisallowMultipleStyleDefinitionsSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

namespace ONGR\Sniffs\CSS;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * ONGR_Sniffs_CSS_DisallowMultipleStyleDefinitionsSniff.
 *
 * Ensure that each style definition is on a line by itself.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class DisallowMultipleStyleDefinitionsSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @var array A list of tokenizers this sniff supports.
     */
    public $supportedTokenizers = ['CSS'];

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return int[]
     */
    public function register()
    {
        return [T_STYLE];
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $next = $phpcsFile->findNext(T_STYLE, ($stackPtr + 1));
        if ($next === false) {
            return;
        }

        if ($tokens[$next]['content'] === 'progid') {
            // Special case for IE filters.
            return;
        }

        if ($tokens[$next]['line'] === $tokens[$stackPtr]['line']) {
            $error = 'Each style definition must be on a line by itself';
            $phpcsFile->addError($error, $next, 'Found');
        }
    }
}
