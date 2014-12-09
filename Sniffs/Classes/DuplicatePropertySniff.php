<?php
/**
 * ONGR_Sniffs_Classes_DuplicatePropertySniff.
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

namespace ONGR\Sniffs\Classes;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * ONGR_Sniffs_Classes_DuplicatePropertySniff.
 *
 * Ensures JS classes don't contain duplicate property names.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class DuplicatePropertySniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @var array A list of tokenizers this sniff supports
     */
    public $supportedTokenizers = ['JS'];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_OBJECT];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The current file being processed.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $start = $tokens[$stackPtr]['scope_opener'];
        $end = $tokens[$stackPtr]['scope_closer'];

        $properties = [];
        $wantedTokens = [
            T_PROPERTY,
            T_OPEN_CURLY_BRACKET,
        ];

        $next = $phpcsFile->findNext($wantedTokens, ($start + 1), $end);
        while ($next !== false && $next < $end) {
            // Skip nested objects.
            if ($tokens[$next]['code'] === T_OPEN_CURLY_BRACKET) {
                $next = $tokens[$next]['bracket_closer'];
            } else {
                $propName = $tokens[$next]['content'];
                if (isset($properties[$propName]) === true) {
                    $error = 'Duplicate property definition found for "%s"; previously defined on line %s';
                    $data = [
                        $propName,
                        $tokens[$properties[$propName]]['line'],
                    ];
                    $phpcsFile->addError($error, $next, 'Found', $data);
                }

                $properties[$propName] = $next;
            }

            $next = $phpcsFile->findNext($wantedTokens, ($next + 1), $end);
        }
    }
}
