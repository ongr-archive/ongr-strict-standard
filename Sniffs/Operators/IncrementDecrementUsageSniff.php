<?php

/**
 * ONGR_Sniffs_Operators_IncrementDecrementUsageSniff.
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

namespace ONGR\Sniffs\Operators;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * ONGR_Sniffs_Operators_IncrementDecrementUsageSniff.
 *
 * Tests that the ++ operators are used when possible and not
 * used when it makes the code confusing.
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
class IncrementDecrementUsageSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_EQUAL,
            T_PLUS_EQUAL,
            T_MINUS_EQUAL,
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

        $assignedVar = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        // Not an assignment, return.
        if ($tokens[$assignedVar]['code'] !== T_VARIABLE) {
            return;
        }

        $statementEnd = $phpcsFile->findNext(
            [T_SEMICOLON, T_CLOSE_PARENTHESIS, T_CLOSE_SQUARE_BRACKET, T_CLOSE_CURLY_BRACKET],
            $stackPtr
        );

        // If there is anything other than variables, numbers, spaces or operators we need to return.
        $noiseTokens = $phpcsFile->findNext(
            [T_LNUMBER, T_VARIABLE, T_WHITESPACE, T_PLUS, T_MINUS, T_OPEN_PARENTHESIS],
            ($stackPtr + 1),
            $statementEnd,
            true
        );

        if ($noiseTokens !== false) {
            return;
        }

        $nextVar = null;
        // If we are already using += or -=, we need to ignore
        // the statement if a variable is being used.
        if ($tokens[$stackPtr]['code'] !== T_EQUAL) {
            $nextVar = $phpcsFile->findNext(T_VARIABLE, ($stackPtr + 1), $statementEnd);
            if ($nextVar !== false) {
                return;
            }
        }

        if ($tokens[$stackPtr]['code'] === T_EQUAL) {
            $nextVar = ($stackPtr + 1);
            $previousVariable = ($stackPtr + 1);
            $variableCount = 0;
            while (($nextVar = $phpcsFile->findNext(T_VARIABLE, ($nextVar + 1), $statementEnd)) !== false) {
                $previousVariable = $nextVar;
                $variableCount++;
            }

            if ($variableCount !== 1) {
                return;
            }

            $nextVar = $previousVariable;
            if ($tokens[$nextVar]['content'] !== $tokens[$assignedVar]['content']) {
                return;
            }
        }

        // We have only one variable, and it's the same as what is being assigned,
        // so we need to check what is being added or subtracted.
        $nextNumber = ($stackPtr + 1);
        $previousNumber = ($stackPtr + 1);
        $numberCount = 0;
        while ((
            $nextNumber = $phpcsFile->findNext([T_LNUMBER], ($nextNumber + 1), $statementEnd, false)) !== false
        ) {
            $previousNumber = $nextNumber;
            $numberCount++;
        }

        if ($numberCount !== 1) {
            return;
        }

        $nextNumber = $previousNumber;
        if ($tokens[$nextNumber]['content'] === '1') {
            if ($tokens[$stackPtr]['code'] === T_EQUAL) {
                $opToken = $phpcsFile->findNext([T_PLUS, T_MINUS], ($nextVar + 1), $statementEnd);
                if ($opToken === false) {
                    // Operator was before the variable, like:
                    // $var = 1 + $var;
                    // So we ignore it.
                    return;
                }

                $operator = $tokens[$opToken]['content'];
            } else {
                $operator = substr($tokens[$stackPtr]['content'], 0, 1);
            }

            // If we are adding or subtracting negative value, the operator
            // needs to be reversed.
            if ($tokens[$stackPtr]['code'] !== T_EQUAL) {
                $negative = $phpcsFile->findPrevious(T_MINUS, ($nextNumber - 1), $stackPtr);
                if ($negative !== false) {
                    $operator = ($operator === '+') ? '-' : '+';
                }
            }

            $expected = $tokens[$assignedVar]['content'] . $operator . $operator;
            $found = $phpcsFile->getTokensAsString($assignedVar, ($statementEnd - $assignedVar + 1));

            if ($operator === '+') {
                $error = 'Increment';
            } else {
                $error = 'Decrement';
            }

            $error .= " operators should be used where possible; found \"$found\" but expected \"$expected\"";
            $phpcsFile->addError($error, $stackPtr);
        }
    }
}
