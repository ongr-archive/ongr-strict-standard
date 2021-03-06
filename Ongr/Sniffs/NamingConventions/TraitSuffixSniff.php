<?php

/**
 * This file is part of the Symfony2-coding-standard (phpcs standard)
 *
 * PHP version 5
 *
 * @category PHP
 * @package PHP_CodeSniffer-Symfony2
 * @author Symfony2-phpcs-authors <Symfony2-coding-standard@escapestudios.github.com>
 * @license http://spdx.org/licenses/MIT MIT License
 * @version GIT: master
 * @link https://github.com/escapestudios/Symfony2-coding-standard
 */

/**
 * Symfony2_Sniffs_NamingConventions_TraitSuffixSniff.
 *
 * Throws errors if trait names are not suffixed with "Trait".
 *
 * @category PHP
 * @package PHP_CodeSniffer
 * @author ONGR Team <info@ongr.io>
 * @copyright 2015 NFQ Technologies UAB
 * @license https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

class Ongr_Sniffs_NamingConventions_TraitSuffixSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @var array A list of tokenizers this sniff supports.
     */
    public $supportedTokenizers = [
        'PHP',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_TRAIT];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile All the tokens found in the document.
     * @param int                  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $line = $tokens[$stackPtr]['line'];
        while ($tokens[$stackPtr]['line'] == $line) {
            if ('T_STRING' == $tokens[$stackPtr]['type']) {
                if (substr($tokens[$stackPtr]['content'], -5) != 'Trait') {
                    $phpcsFile->addError(
                        'Trait name is not suffixed with "Trait"',
                        $stackPtr
                    );
                }
                break;
            }
            $stackPtr++;
        }
    }
}
