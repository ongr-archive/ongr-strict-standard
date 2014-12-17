<?php
/**
 * A test to ensure that arrays conform to the array coding standard.
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

namespace ONGR\Sniffs\Arrays;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;
use PHP_CodeSniffer_Tokens;

/**
 * A test to ensure that arrays conform to the array coding standard.
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
class ArrayDeclarationSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_ARRAY,
            T_OPEN_SHORT_ARRAY,
        ];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The current file being checked.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['code'] !== T_OPEN_SHORT_ARRAY) {
            $phpcsFile->addError('Must use short array syntax for arrays', $stackPtr, 'LongArray');

            return;
        }

        $arrayStart = $tokens[$stackPtr]['bracket_opener'];
        $arrayEnd = $tokens[$arrayStart]['bracket_closer'];

        $statementStartColumn = $this->getStatementStartColumn($phpcsFile, $stackPtr);

        // Check for empty arrays.
        $content = $phpcsFile->findNext([T_WHITESPACE], ($arrayStart + 1), ($arrayEnd + 1), true);
        if ($content === $arrayEnd) {
            // Empty array, but if the brackets aren't together, there's a problem.
            if (($arrayEnd - $arrayStart) !== 1) {
                $error = 'Empty array declaration must have no space between the parentheses';
                $phpcsFile->addError($error, $stackPtr, 'SpaceInEmptyArray');
            }
            // We can return here because there is nothing else to check. All code
            // below can assume that the array is not empty.
            return;
        }

        if ($tokens[$arrayStart]['line'] === $tokens[$arrayEnd]['line']) {
            // Single line array.
            // Check if there are multiple values. If so, then it has to be multiple lines
            // unless it is contained inside a function call or condition.
            $valueCount = 0;
            $commas = [];
            for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
                // Skip bracketed statements, like function calls.
                if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                    $i = $tokens[$i]['parenthesis_closer'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_COMMA) {
                    // Before counting this comma, make sure we are not
                    // at the end of the array.
                    $next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), $arrayEnd, true);
                    if ($next !== false) {
                        $valueCount++;
                        $commas[] = $i;
                    } else {
                        // There is a comma at the end of a single line array.
                        $error = 'Comma not allowed after last value in single-line array declaration';
                        $phpcsFile->addError($error, $i, 'CommaAfterLast');
                    }
                }
            }

            // Now check each of the double arrows (if any).
            $nextArrow = $arrayStart;
            while ((
                $nextArrow = $phpcsFile->findNext(
                    T_DOUBLE_ARROW,
                    ($nextArrow + 1),
                    $arrayEnd
                )) !== false
            ) {
                if ($tokens[($nextArrow - 1)]['code'] !== T_WHITESPACE) {
                    $content = $tokens[($nextArrow - 1)]['content'];
                    $error = 'Expected 1 space between "%s" and double arrow; 0 found';
                    $data = [$content];
                    $phpcsFile->addError($error, $nextArrow, 'NoSpaceBeforeDoubleArrow', $data);
                } else {
                    $spaceLength = strlen($tokens[($nextArrow - 1)]['content']);
                    if ($spaceLength !== 1) {
                        $content = $tokens[($nextArrow - 2)]['content'];
                        $error = 'Expected 1 space between "%s" and double arrow; %s found';
                        $data = [
                            $content,
                            $spaceLength,
                        ];
                        $phpcsFile->addError($error, $nextArrow, 'SpaceBeforeDoubleArrow', $data);
                    }
                }

                if ($tokens[($nextArrow + 1)]['code'] !== T_WHITESPACE) {
                    $content = $tokens[($nextArrow + 1)]['content'];
                    $error = 'Expected 1 space between double arrow and "%s"; 0 found';
                    $data = [$content];
                    $phpcsFile->addError($error, $nextArrow, 'NoSpaceAfterDoubleArrow', $data);
                } else {
                    $spaceLength = strlen($tokens[($nextArrow + 1)]['content']);
                    if ($spaceLength !== 1) {
                        $content = $tokens[($nextArrow + 2)]['content'];
                        $error = 'Expected 1 space between double arrow and "%s"; %s found';
                        $data = [
                            $content,
                            $spaceLength,
                        ];
                        $phpcsFile->addError($error, $nextArrow, 'SpaceAfterDoubleArrow', $data);
                    }
                }
            }

            if ($valueCount > 0) {
                // We have a multiple value array that is inside a condition or
                // function. Check its spacing is correct.
                foreach ($commas as $comma) {
                    if ($tokens[($comma + 1)]['code'] !== T_WHITESPACE) {
                        $content = $tokens[($comma + 1)]['content'];
                        $error = 'Expected 1 space between comma and "%s"; 0 found';
                        $data = [$content];
                        $phpcsFile->addError($error, $comma, 'NoSpaceAfterComma', $data);
                    } else {
                        $spaceLength = strlen($tokens[($comma + 1)]['content']);
                        if ($spaceLength !== 1) {
                            $content = $tokens[($comma + 2)]['content'];
                            $error = 'Expected 1 space between comma and "%s"; %s found';
                            $data = [
                                $content,
                                $spaceLength,
                            ];
                            $phpcsFile->addError($error, $comma, 'SpaceAfterComma', $data);
                        }
                    }

                    if ($tokens[($comma - 1)]['code'] === T_WHITESPACE) {
                        $content = $tokens[($comma - 2)]['content'];
                        $spaceLength = strlen($tokens[($comma - 1)]['content']);
                        $error = 'Expected 0 spaces between "%s" and comma; %s found';
                        $data = [
                            $content,
                            $spaceLength,
                        ];
                        $phpcsFile->addError($error, $comma, 'SpaceBeforeComma', $data);
                    }
                }
            }

            return;
        }

        // Check the closing bracket is on a new line.
        $lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($arrayEnd - 1), $arrayStart, true);
        if ($tokens[$lastContent]['line'] !== ($tokens[$arrayEnd]['line'] - 1)) {
            $error = 'Closing parenthesis of array declaration must be on a new line';
            $phpcsFile->addError($error, $arrayEnd, 'CloseBraceNewLine');
        } elseif ($tokens[$arrayEnd]['column'] !== $statementStartColumn) {
            // Check the closing bracket is lined up under the a in array.
            $expected = $statementStartColumn;
            $found = $tokens[$arrayEnd]['column'];
            $error = 'Closing parenthesis not aligned correctly; expected %s space(s) but found %s';
            $data = [
                $expected,
                $found,
            ];
            $phpcsFile->addError($error, $arrayEnd, 'CloseBraceNotAligned', $data);
        }

        $nextToken = $stackPtr;
        $keyUsed = false;
        $singleUsed = false;
        $lastToken = '';
        $indices = [];

        // Find all the double arrows that reside in this scope.
        while ((
            $nextToken = $phpcsFile->findNext(
                [T_DOUBLE_ARROW, T_COMMA, T_ARRAY, T_OPEN_SHORT_ARRAY, T_OPEN_PARENTHESIS],
                ($nextToken + 1),
                $arrayEnd
            )) !== false
        ) {
            $currentEntry = [];
            if ($tokens[$nextToken]['code'] === T_ARRAY || $tokens[$nextToken]['code'] == T_OPEN_SHORT_ARRAY
                || $tokens[$nextToken]['code'] === T_OPEN_PARENTHESIS
            ) {
                // Skip contents in parenthesis and let subsequent calls of this test handle nested arrays.
                if ($tokens[$nextToken - 2]['code'] == T_CLOSURE) {
                    $nextToken -= 2;
                }
                $indices[] = ['value' => $nextToken];
                if ($tokens[$nextToken]['code'] == T_OPEN_SHORT_ARRAY) {
                    $nextToken = $tokens[$nextToken]['bracket_closer'];
                } else {
                    $nextToken = $tokens[$nextToken]['parenthesis_closer'];
                }
                // If array declaration is the only thing on last line other checks
                // will not have any chance to check for comma so we need to do it now.
                $multiLine = $tokens[$arrayStart]['line'] !== $tokens[$arrayEnd]['line'];
                $next = $phpcsFile->findNext(
                    [T_DOUBLE_ARROW, T_COMMA, T_ARRAY, T_OPEN_SHORT_ARRAY, T_OPEN_PARENTHESIS],
                    $nextToken,
                    $arrayEnd
                );
                if ($multiLine && !$next) {
                    // There is no more content which means missing comma.
                    $error = 'Each line in an array declaration must end with a comma';
                    $phpcsFile->addError($error, $nextToken, 'NoCommaAfterLast');

                    return;
                }
                continue;
            }

            if ($tokens[$nextToken]['code'] === T_COMMA) {
                if ($keyUsed === true && $lastToken === T_COMMA) {
                    $error = 'No key specified for array entry; first entry specifies key';
                    $phpcsFile->addError($error, $nextToken, 'NoKeySpecified');

                    return;
                }

                if ($keyUsed === false) {
                    if ($tokens[($nextToken - 1)]['code'] === T_WHITESPACE) {
                        $content = $tokens[($nextToken - 2)]['content'];
                        $spaceLength = strlen($tokens[($nextToken - 1)]['content']);
                        $error = 'Expected 0 spaces between "%s" and comma; %s found';
                        $data = [
                            $content,
                            $spaceLength,
                        ];
                        $phpcsFile->addError($error, $nextToken, 'SpaceBeforeComma', $data);
                    }

                    // Find the value, which will be the first token on the line,
                    // excluding the leading whitespace.
                    $valueContent = $phpcsFile->findPrevious(
                        PHP_CodeSniffer_Tokens::$emptyTokens,
                        ($nextToken - 1),
                        null,
                        true
                    );

                    while ($tokens[$valueContent]['line'] === $tokens[$nextToken]['line']) {
                        if ($valueContent === $arrayStart) {
                            // Value must have been on the same line as the array
                            // parenthesis, so we have reached the start of the value.

                            break;
                        }

                        $valueContent--;
                    }
                    $valueContent = $phpcsFile->findNext(T_WHITESPACE, ($valueContent + 1), $nextToken, true);
                    $indices[] = ['value' => $valueContent];
                    $singleUsed = true;
                }

                $lastToken = T_COMMA;
                continue;
            }

            if ($tokens[$nextToken]['code'] === T_DOUBLE_ARROW) {
                if ($singleUsed === true) {
                    $error = 'Key specified for array entry; first entry has no key';
                    $phpcsFile->addError($error, $nextToken, 'KeySpecified');

                    return;
                }

                if ($tokens[$nextToken - 1]['code'] !== T_WHITESPACE
                    || strlen($tokens[$nextToken - 1]['content']) != 1
                ) {
                    $error = 'Expected single whitespace before double arrow, found %s spaces';
                    if ($tokens[$nextToken - 1]['code'] !== T_WHITESPACE) {
                        $data = [0];
                    } else {
                        $data = [strlen($tokens[$nextToken - 1]['content'])];
                    }
                    $phpcsFile->addError($error, $nextToken - 1, 'DoubleArrowSpacing', $data);
                }

                $currentEntry['arrow'] = $nextToken;
                $keyUsed = true;

                // Find the start of index that uses this double arrow.
                $indexEnd = $phpcsFile->findPrevious(T_WHITESPACE, ($nextToken - 1), $arrayStart, true);
                $indexStart = $phpcsFile->findPrevious(T_WHITESPACE, $indexEnd, $arrayStart);

                if ($indexStart === false) {
                    $index = $indexEnd;
                } else {
                    $index = ($indexStart + 1);
                }

                $currentEntry['index'] = $index;
                $currentEntry['index_content'] = $phpcsFile->getTokensAsString($index, ($indexEnd - $index + 1));

                // Find the value of this index.
                $nextContent = $phpcsFile->findNext([T_WHITESPACE], ($nextToken + 1), $arrayEnd, true);
                $currentEntry['value'] = $nextContent;
                $indices[] = $currentEntry;
                $lastToken = T_DOUBLE_ARROW;
            }
        }

        /*
            This section checks for arrays that don't specify keys.

            Arrays such as:
               array(
                'aaa',
                'bbb',
                'd',
               );
        */

        if (empty($indices)) {
            // Multi-line array is not empty and nothing but 1 value was found.
            $value = $arrayStart + 1;
            while ($tokens[$value]['code'] == T_WHITESPACE) {
                $value++;
            }
            $error = 'Each line in an array declaration must end with a comma';
            $phpcsFile->addError($error, $value, 'NoComma');
        }

        if ($keyUsed === false && empty($indices) === false) {
            $count = count($indices);
            $lastIndex = $indices[($count - 1)]['value'];

            $trailingContent = $phpcsFile->findPrevious(T_WHITESPACE, ($arrayEnd - 1), $lastIndex, true);
            if ($tokens[$trailingContent]['code'] !== T_COMMA) {
                $error = 'Comma required after last value in array declaration';
                $phpcsFile->addError($error, $trailingContent, 'NoCommaAfterLast');
            }

            foreach ($indices as $value) {
                if (empty($value['value']) === true) {
                    // Array was malformed and we couldn't figure out
                    // the array value correctly, so we have to ignore it.
                    // Other parts of this sniff will correct the error.
                    continue;
                }

                if ($tokens[($value['value'] - 1)]['code'] === T_WHITESPACE) {
                    // A whitespace token before this value means that the value
                    // was indented and not flush with the opening parenthesis.
                    if ($tokens[$value['value']]['column'] !== ($statementStartColumn + 4)) {
                        $error = 'Array value not aligned correctly; expected %s spaces but found %s';
                        $data = [
                            ($statementStartColumn + 4),
                            $tokens[$value['value']]['column'],
                        ];
                        $phpcsFile->addError($error, $value['value'], 'ValueNotAligned', $data);
                    }
                }
            }
        }

        /*
            Below the actual indentation of the array is checked.
            Errors will be thrown when a key is not aligned, when
            a double arrow is not aligned, and when a value is not
            aligned correctly.
            If an error is found in one of the above areas, then errors
            are not reported for the rest of the line to avoid reporting
            spaces and columns incorrectly. Often fixing the first
            problem will fix the other 2 anyway.

            For example:

            $a = array(
                  'index'  => '2',
                 );

            In this array, the double arrow is indented too far, but this
            will also cause an error in the value's alignment. If the arrow were
            to be moved back one space however, then both errors would be fixed.
        */

        $indicesStart = ($statementStartColumn + 4);
        foreach ($indices as $index) {
            if (isset($index['index']) === false) {
                // Array value only.
                if (($tokens[$index['value']]['line'] === $tokens[$stackPtr]['line'])) {
                    $error = 'The first value in a multi-value array must be on a new line';
                    $phpcsFile->addError($error, $stackPtr, 'FirstValueNoNewline');
                }

                continue;
            }

            if (($tokens[$index['index']]['line'] === $tokens[$stackPtr]['line'])) {
                $error = 'The first index in a multi-value array must be on a new line';
                $phpcsFile->addError($error, $stackPtr, 'FirstIndexNoNewline');
                continue;
            }

            if ($tokens[$index['index']]['column'] !== $indicesStart) {
                $error = 'Array key not aligned correctly; expected %s spaces but found %s';
                $data = [
                    ($indicesStart - 1),
                    ($tokens[$index['index']]['column'] - 1),
                ];
                $phpcsFile->addError($error, $index['index'], 'KeyNotAligned', $data);
                continue;
            }

            // Check each line ends with a comma.
            $valueLine = $tokens[$index['value']]['line'];
            $nextComma = false;
            for ($i = ($index['value'] + 1); $i < $arrayEnd; $i++) {
                // Skip bracketed statements, like function calls.
                if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                    $i = $tokens[$i]['parenthesis_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_COMMA) {
                    $nextComma = $i;
                    break;
                }
            }

            if (($nextComma === false)
                || ($tokens[$nextComma]['line'] !== $valueLine
                && $tokens[$index['value']]['code'] !== T_OPEN_SHORT_ARRAY)
            ) {
                $error = 'Each line in an array declaration must end with a comma';
                $phpcsFile->addError($error, $index['value'], 'NoComma');
            }

            // Check that there is no space before the comma.
            if ($nextComma !== false && $tokens[($nextComma - 1)]['code'] === T_WHITESPACE) {
                $content = $tokens[($nextComma - 2)]['content'];
                $spaceLength = strlen($tokens[($nextComma - 1)]['content']);
                $error = 'Expected 0 spaces between "%s" and comma; %s found';
                $data = [
                    $content,
                    $spaceLength,
                ];
                $phpcsFile->addError($error, $nextComma, 'SpaceBeforeComma', $data);
            }
        }
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $stackPtr
     *
     * @return mixed
     */
    private function getStatementStartColumn(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $line = $tokens[$stackPtr]['line'];

        do {
            $stackPtr--;
        } while ($tokens[$stackPtr]['line'] == $line);

        do {
            $stackPtr++;
        } while ($tokens[$stackPtr]['code'] == T_WHITESPACE);

        return $tokens[$stackPtr]['column'];
    }
}
