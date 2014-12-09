<?php
/**
 * ONGR_Sniffs_CSS_DuplicateStyleDefinitionSniff.
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
 * ONGR_Sniffs_CSS_DuplicateStyleDefinitionSniff.
 *
 * Check for duplicate style definitions in the same class.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class DuplicateStyleDefinitionSniff implements PHP_CodeSniffer_Sniff
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
        return [T_OPEN_CURLY_BRACKET];
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

        // Find the content of each style definition name.
        $end = $tokens[$stackPtr]['bracket_closer'];
        $next = $phpcsFile->findNext(T_STYLE, ($stackPtr + 1), $end);
        if ($next === false) {
            // Class definition is empty.
            return;
        }

        $styleNames = [];

        while ($next !== false) {
            $name = $tokens[$next]['content'];
            if (isset($styleNames[$name]) === true) {
                $first = $styleNames[$name];
                $error = 'Duplicate style definition found; first defined on line %s';
                $data = [$tokens[$first]['line']];
                $phpcsFile->addError($error, $next, 'Found', $data);
            } else {
                $styleNames[$name] = $next;
            }

            $next = $phpcsFile->findNext(T_STYLE, ($next + 1), $end);
        }
    }
}
