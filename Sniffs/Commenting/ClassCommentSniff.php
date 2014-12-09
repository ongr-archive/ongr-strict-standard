<?php
/**
 * Parses and verifies the class doc comment.
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

namespace ONGR\Sniffs\Commenting;

use PHP_CodeSniffer_CommentParser_ClassCommentParser;
use PHP_CodeSniffer_CommentParser_CommentElement;
use PHP_CodeSniffer_CommentParser_ParserException;
use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Parses and verifies the class doc comment.
 *
 * Verifies that :
 * <ul>
 *  <li>A class doc comment exists.</li>
 *  <li>There is exactly one blank line before the class comment.</li>
 *  <li>Short description ends with a full stop.</li>
 *  <li>There is a blank line after the short description.</li>
 *  <li>Each paragraph of the long description ends with a full stop.</li>
 *  <li>There is a blank line between the description and the tags.</li>
 *  <li>Check the format of the since tag (x.x.x).</li>
 * </ul>
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
class ClassCommentSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @var int
     */
    private $currentFile;

    /**
     * @var mixed
     */
    private $commentParser;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_CLASS,
            T_TRAIT,
            T_INTERFACE,
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
        $this->currentFile = $phpcsFile;

        $className = $phpcsFile->getDeclarationName($stackPtr);
        $isTestClass = preg_match('/Test$/', $className) === 1;

        $tokens = $phpcsFile->getTokens();
        $name = ucfirst($tokens[$stackPtr]['content']);

        $find = [
            T_ABSTRACT,
            T_WHITESPACE,
            T_FINAL,
        ];

        // Extract the class comment docblock.
        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);

        if ($commentEnd !== false && $tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError("You must use \"/**\" style comments for a {$name} comment", $stackPtr, 'WrongStyle');

            return;
        } elseif ($commentEnd === false || $tokens[$commentEnd]['code'] !== T_DOC_COMMENT) {
            if (!$isTestClass) {
                // Test classes can omit doc comment.
                $phpcsFile->addError("Missing {$name} doc comment", $stackPtr, 'Missing');
            }

            return;
        }

        $commentStart = ($phpcsFile->findPrevious(T_DOC_COMMENT, ($commentEnd - 1), null, true) + 1);

        // Distinguish file and class comment.
        $prevClassToken = $phpcsFile->findPrevious(T_CLASS, ($stackPtr - 1));
        if ($prevClassToken === false) {
            // This is the first class token in this file, need extra checks.
            $prevNonComment = $phpcsFile->findPrevious(T_DOC_COMMENT, ($commentStart - 1), null, true);
            if ($prevNonComment !== false) {
                $prevComment = $phpcsFile->findPrevious(T_DOC_COMMENT, ($prevNonComment - 1));
                if ($prevComment === false) {
                    // There is only 1 doc comment between open tag and class token.
                    $newlineToken = $phpcsFile->findNext(
                        T_WHITESPACE,
                        ($commentEnd + 1),
                        $stackPtr,
                        false,
                        $phpcsFile->eolChar
                    );
                    if ($newlineToken !== false) {
                        $newlineToken = $phpcsFile->findNext(
                            T_WHITESPACE,
                            ($newlineToken + 1),
                            $stackPtr,
                            false,
                            $phpcsFile->eolChar
                        );
                        if ($newlineToken !== false) {
                            // Blank line between the class and the doc block.
                            // The doc block is most likely a file comment.
                            if (!$isTestClass) {
                                // Test classes can omit doc comment.
                                $phpcsFile->addError("Missing {$name} doc comment", ($stackPtr + 1), 'Missing');
                            }

                            return;
                        }
                    }
                }

                // Exactly one blank line before the class comment.
                $prevTokenEnd = $phpcsFile->findPrevious(T_WHITESPACE, ($commentStart - 1), null, true);
                if ($tokens[$prevTokenEnd]['line'] !== ($tokens[$commentStart]['line'] - 2)) {
                    $error = "There must be exactly one blank line before the {$name} comment";
                    $phpcsFile->addError($error, ($commentStart - 1), 'SpacingBefore');
                }
            }
        }

        $commentString = $phpcsFile->getTokensAsString($commentStart, ($commentEnd - $commentStart + 1));

        // Parse the class comment docblock.
        try {
            $this->commentParser = new PHP_CodeSniffer_CommentParser_ClassCommentParser($commentString, $phpcsFile);
            $this->commentParser->parse();
        } catch (PHP_CodeSniffer_CommentParser_ParserException $e) {
            $line = ($e->getLineWithinComment() + $commentStart);
            $phpcsFile->addError($e->getMessage(), $line, 'FailedParse');

            return;
        }

        /** @var PHP_CodeSniffer_CommentParser_CommentElement $comment */
        $comment = $this->commentParser->getComment();
        if ($comment === null) {
            $error = "{$name} doc comment is empty";
            $phpcsFile->addError($error, $commentStart, 'Empty');

            return;
        }

        // The first line of the comment should just be the /** code.
        $eolPos = strpos($commentString, $phpcsFile->eolChar);
        $firstLine = substr($commentString, 0, $eolPos);
        if ($firstLine !== '/**') {
            $error = 'The open comment tag must be the only content on the line';
            $phpcsFile->addError($error, $commentStart, 'SpacingAfterOpen');
        }

        // Check for a comment description.
        $short = rtrim($comment->getShortComment(), $phpcsFile->eolChar);
        if (trim($short) === '') {
            $error = "Missing short description in {$name} doc comment";
            $phpcsFile->addError($error, $commentStart, 'MissingShort');

            return;
        }

        // No extra newline before short description.
        $newlineSpan = strspn($short, $phpcsFile->eolChar);
        if ($short !== '' && $newlineSpan > 0) {
            $error = "Extra newline(s) found before {$name} comment short description";
            $phpcsFile->addError($error, ($commentStart + 1), 'SpacingBeforeShort');
        }

        $newlineCount = (substr_count($short, $phpcsFile->eolChar) + 1);

        // Exactly one blank line between short and long description.
        $long = $comment->getLongComment();
        if (empty($long) === false) {
            $between = $comment->getWhiteSpaceBetween();
            $newlineBetween = substr_count($between, $phpcsFile->eolChar);
            if ($newlineBetween !== 2) {
                $error = "There must be exactly one blank line between descriptions in {$name} comment";
                $phpcsFile->addError($error, ($commentStart + $newlineCount + 1), 'SpacingBetween');
            }

            $newlineCount += $newlineBetween;

            $testLong = trim($long);
            if (preg_match('|\p{Lu}|u', $testLong[0]) === 0) {
                $error = "{$name} comment long description must start with a capital letter";
                $phpcsFile->addError($error, ($commentStart + $newlineCount), 'LongNotCapital');
            }
        }

        // Exactly one blank line before tags.
        $tags = $this->commentParser->getTagOrders();
        if (count($tags) > 1) {
            $newlineSpan = $comment->getNewlineAfter();
            if ($newlineSpan !== 2) {
                $error = "There must be exactly one blank line before the tags in {$name} comment";
                if ($long !== '') {
                    $newlineCount += (substr_count($long, $phpcsFile->eolChar) - $newlineSpan + 1);
                }

                $phpcsFile->addError($error, ($commentStart + $newlineCount), 'SpacingBeforeTags');
                $short = rtrim($short, $phpcsFile->eolChar . ' ');
            }
        }

        // Short description must be single line and end with a full stop.
        $testShort = trim($short);
        $lastChar = $testShort[(strlen($testShort) - 1)];
        if (substr_count($testShort, $phpcsFile->eolChar) !== 0) {
            $error = "{$name} comment short description must be on a single line";
            $phpcsFile->addError($error, ($commentStart + 1), 'ShortSingleLine');
        }
        if (preg_match('|\p{Lu}|u', $testShort[0]) === 0) {
            $error = "{$name} comment short description must start with a capital letter";
            $phpcsFile->addError($error, ($commentStart + 1), 'ShortNotCapital');
        }

        if ($lastChar !== '.') {
            $error = "{$name} comment short description must end with a full stop";
            $phpcsFile->addError($error, ($commentStart + 1), 'ShortFullStop');
        }

        // The last content should be a newline and the content before
        // that should not be blank. If there is more blank space
        // then they have additional blank lines at the end of the comment.
        $words = $this->commentParser->getWords();
        $lastPos = (count($words) - 1);
        if (trim($words[($lastPos - 1)]) !== ''
            || strpos($words[($lastPos - 1)], $this->currentFile->eolChar) === false
            || trim($words[($lastPos - 2)]) === ''
        ) {
            $error = "Additional blank lines found at end of {$name} comment";
            $this->currentFile->addError($error, $commentEnd, 'SpacingAfter');
        }
    }
}
