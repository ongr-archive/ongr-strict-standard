<?php
/**
 * Parses and verifies the doc comments for functions.
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

use PHP_CodeSniffer;
use PHP_CodeSniffer_CommentParser_CommentElement;
use PHP_CodeSniffer_CommentParser_FunctionCommentParser;
use PHP_CodeSniffer_CommentParser_PairElement;
use PHP_CodeSniffer_CommentParser_ParameterElement;
use PHP_CodeSniffer_CommentParser_ParserException;
use PHP_CodeSniffer_CommentParser_SingleElement;
use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;
use PHP_CodeSniffer_Tokens;

/**
 * Parses and verifies the doc comments for functions.
 *
 * Verifies that :
 * <ul>
 *  <li>A comment exists</li>
 *  <li>There is a blank newline after the short description</li>
 *  <li>There is a blank newline between the long and short description</li>
 *  <li>There is a blank newline between the long description and tags</li>
 *  <li>Parameter names represent those in the method</li>
 *  <li>Parameter comments are in the correct order</li>
 *  <li>Parameter comments are complete</li>
 *  <li>A type hint is provided for array and custom class</li>
 *  <li>Type hint matches the actual variable/class type</li>
 *  <li>A blank line is present before the first and after the last parameter</li>
 *  <li>A return type exists</li>
 *  <li>Any throw tag must have a comment</li>
 *  <li>The tag order and indentation are correct</li>
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
class FunctionCommentSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @var string The name of the method that we are currently processing.
     */
    private $methodName = '';

    /**
     * @var int The position in the stack where the function token was found.
     */
    private $functionToken = null;

    /**
     * @var int The position in the stack where the class token was found.
     */
    private $classToken = null;

    /**
     * @var int The index of the current tag we are processing.
     */
    private $tagIndex = 0;

    /**
     * @var PHP_CodeSniffer_CommentParser_FunctionCommentParser The function comment parser for the current method.
     */
    protected $commentParser = null;

    /**
     * @var PHP_CodeSniffer_File The current PHP_CodeSniffer_File object we are processing.
     */
    protected $currentFile = null;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_FUNCTION];
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

        $tokens = $phpcsFile->getTokens();

        if ($this->isFunctionIgnored($tokens[$phpcsFile->findNext(T_STRING, $stackPtr)]['content'])) {
            return;
        }

        $find = [
            T_COMMENT,
            T_DOC_COMMENT,
            T_CLASS,
            T_FUNCTION,
            T_OPEN_TAG,
        ];

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1));

        if ($commentEnd === false) {
            return;
        }

        // If the token that we found was a class or a function, then this
        // function has no doc comment.
        $code = $tokens[$commentEnd]['code'];

        if ($code === T_COMMENT) {
            // The function might actually be missing a comment, and this last comment
            // found is just commenting a bit of code on a line. So if it is not the
            // only thing on the line, assume we found nothing.
            if ($tokens[$commentEnd]['line'] === $tokens[$commentEnd]['line']) {
                $error = 'Missing function doc comment';
                $phpcsFile->addError($error, $stackPtr, 'Missing');
            } else {
                $error = 'You must use "/**" style comments for a function comment';
                $phpcsFile->addError($error, $stackPtr, 'WrongStyle');
            }

            return;
        } elseif ($code !== T_DOC_COMMENT) {
            $error = 'Missing function doc comment';
            $phpcsFile->addError($error, $stackPtr, 'Missing');

            return;
        }  if (trim($tokens[$commentEnd]['content']) !== '*/') {
            $error = 'You must use "*/" to end a function comment; found "%s"';
            $phpcsFile->addError($error, $commentEnd, 'WrongEnd', [trim($tokens[$commentEnd]['content'])]);

            return;
        }

        // If there is any code between the function keyword and the doc block
        // then the doc block is not for us.
        $ignore = PHP_CodeSniffer_Tokens::$scopeModifiers;
        $ignore[] = T_STATIC;
        $ignore[] = T_WHITESPACE;
        $ignore[] = T_ABSTRACT;
        $ignore[] = T_FINAL;
        $prevToken = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);
        if ($prevToken !== $commentEnd) {
            $phpcsFile->addError('Missing function doc comment', $stackPtr, 'Missing');

            return;
        }

        $this->functionToken = $stackPtr;

        $this->classToken = null;
        foreach ($tokens[$stackPtr]['conditions'] as $condPtr => $condition) {
            if ($condition === T_CLASS || $condition === T_INTERFACE) {
                $this->classToken = $condPtr;
                break;
            }
        }

        // Find the first doc comment.
        $commentStart = ($phpcsFile->findPrevious(T_DOC_COMMENT, ($commentEnd - 1), null, true) + 1);
        $commentString = $phpcsFile->getTokensAsString($commentStart, ($commentEnd - $commentStart + 1));
        $this->methodName = $phpcsFile->getDeclarationName($stackPtr);

        $this->checkForExtraWhiteSpace(array_slice($tokens, $commentStart, $commentEnd - $commentStart, true));

        try {
            $this->commentParser = new PHP_CodeSniffer_CommentParser_FunctionCommentParser($commentString, $phpcsFile);
            $this->commentParser->parse();
        } catch (PHP_CodeSniffer_CommentParser_ParserException $e) {
            $line = ($e->getLineWithinComment() + $commentStart);
            $phpcsFile->addError($e->getMessage(), $line, 'FailedParse');

            return;
        }

        /** @var PHP_CodeSniffer_CommentParser_CommentElement $comment */
        $comment = $this->commentParser->getComment();
        if ($comment === null) {
            $error = 'Function doc comment is empty';
            $phpcsFile->addError($error, $commentStart, 'Empty');

            return;
        }

        // The first line of the comment should just be the /** code.
        $eolPos = strpos($commentString, $phpcsFile->eolChar);
        $firstLine = substr($commentString, 0, $eolPos);
        if ($firstLine !== '/**') {
            $error = 'The open comment tag must be the only content on the line';
            $phpcsFile->addError($error, $commentStart, 'ContentAfterOpen');
        }

        $short = $comment->getShortComment();
        $allComments = trim($short . ' ' . $comment->getLongComment());

        // Ignore doc tag rules if it contains only {@inheritdoc}.
        $inheritdocMatches = [];
        if (preg_match('#{@inheritdoc}#i', $allComments, $inheritdocMatches) !== 1) {
            $this->processParams($commentStart, $commentEnd);
            $this->processSees($commentStart);
            $this->processReturn($commentStart, $commentEnd);
            $this->processThrows($commentStart);
        } else {
            if ($inheritdocMatches[0] !== '{@inheritdoc}') {
                $error = '{@inheritdoc} must be spelled lowercase';
                $phpcsFile->addError($error, ($commentStart + 1), 'InheritdocMisspell');
            }
        }

        // Check for a comment description.
        if (trim($short) === '') {
            if (preg_match('/^(set|get|has|add|is)[A-Z]|__construct/', $this->methodName) !== 1) {
                $error = 'Missing short description in function doc comment';
                $phpcsFile->addError($error, $commentStart, 'MissingShort');
            }

            return;
        }

        // No extra newline before short description.
        $newlineSpan = strspn($short, $phpcsFile->eolChar);
        if ($short !== '' && $newlineSpan > 0) {
            $error = 'Extra newline(s) found before function comment short description';
            $phpcsFile->addError($error, ($commentStart + 1), 'SpacingBeforeShort');
        }

        $newlineCount = (substr_count($short, $phpcsFile->eolChar) + 1);

        // Exactly one blank line between short and long description.
        $long = $comment->getLongComment();
        if (empty($long) === false) {
            $between = $comment->getWhiteSpaceBetween();
            $newlineBetween = substr_count($between, $phpcsFile->eolChar);
            if ($newlineBetween !== 2) {
                $error = 'There must be exactly one blank line between descriptions in function comment';
                $phpcsFile->addError($error, ($commentStart + $newlineCount + 1), 'SpacingBetween');
            }

            $newlineCount += $newlineBetween;

            $testLong = trim($long);
            if (preg_match('#\p{Lu}||{@inheritdoc}#u', $testLong[0]) === 0) {
                $error = 'Function comment long description must start with a capital letter';
                $phpcsFile->addError($error, ($commentStart + $newlineCount), 'LongNotCapital');
            }
        }

        // Exactly one blank line before tags.
        $params = $this->commentParser->getTagOrders();
        if (count($params) > 1) {
            $newlineSpan = $comment->getNewlineAfter();
            if ($newlineSpan !== 2) {
                $error = 'There must be exactly one blank line before the tags in function comment';
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
            $error = 'Function comment short description must be on a single line';
            $phpcsFile->addError($error, ($commentStart + 1), 'ShortSingleLine');
        }

        if (preg_match('#^(\p{Lu}|{@inheritdoc})#u', $testShort) === 0) {
            $error = 'Function comment short description must start with a capital letter';
            $phpcsFile->addError($error, ($commentStart + 1), 'ShortNotCapital');
        }

        if (preg_match('#{@inheritdoc}$#u', $testShort) === 0 && $lastChar !== '.') {
            $error = 'Function comment short description must end with a full stop';
            $phpcsFile->addError($error, ($commentStart + 1), 'ShortFullStop');
        }

        // Check for unknown/deprecated tags.
        $this->processUnknownTags($commentStart, $commentEnd);

        // The last content should be a newline and the content before
        // that should not be blank. If there is more blank space
        // then they have additional blank lines at the end of the comment.
        $words = $this->commentParser->getWords();
        $lastPos = (count($words) - 1);
        if (trim($words[($lastPos - 1)]) !== ''
            || strpos($words[($lastPos - 1)], $this->currentFile->eolChar) === false
            || trim($words[($lastPos - 2)]) === ''
        ) {
            $error = 'Additional blank lines found at end of function comment';
            $this->currentFile->addError($error, $commentEnd, 'SpacingAfter');
        }
    }

    /**
     * Process the see tags.
     *
     * @param int $commentStart The position in the stack where the comment started.
     *
     * @return void
     */
    protected function processSees($commentStart)
    {
        /** @var PHP_CodeSniffer_CommentParser_SingleElement[] $sees */
        $sees = $this->commentParser->getSees();
        if (empty($sees) === false) {
            $tagOrder = $this->commentParser->getTagOrders();
            $index = array_keys($this->commentParser->getTagOrders(), 'see');
            foreach ($sees as $i => $see) {
                $errorPos = ($commentStart + $see->getLine());
                $since = array_keys($tagOrder, 'since');
                if (count($since) === 1 && $this->tagIndex !== 0) {
                    $this->tagIndex++;
                    if ($index[$i] !== $this->tagIndex) {
                        $error = 'The @see tag is in the wrong order; the tag precedes @return';
                        $this->currentFile->addError($error, $errorPos, 'SeeOrder');
                    }
                }

                $content = $see->getContent();
                if (empty($content) === true) {
                    $error = 'Content missing for @see tag in function comment';
                    $this->currentFile->addError($error, $errorPos, 'EmptySee');
                    continue;
                }

                $spacing = substr_count($see->getWhitespaceBeforeContent(), ' ');
                if ($spacing !== 1) {
                    $error = '@see tag indented incorrectly; expected 1 space but found %s';
                    $data = [$spacing];
                    $this->currentFile->addError($error, $errorPos, 'SeeIndent', $data);
                }
            }
        }
    }

    /**
     * Process the return comment of this function comment.
     *
     * @param int $commentStart The position in the stack where the comment started.
     * @param int $commentEnd   The position in the stack where the comment ended.
     *
     * @return void
     */
    protected function processReturn($commentStart, $commentEnd)
    {
        // Skip constructor and destructor.
        $className = '';
        if ($this->classToken !== null) {
            $className = $this->currentFile->getDeclarationName($this->classToken);
            $className = strtolower(ltrim($className, '_'));
        }

        $methodName = strtolower(ltrim($this->methodName, '_'));
        if ($methodName === '') {
            $methodName = $this->methodName;
        }

        $isSpecialMethod = ($this->methodName === '__construct' || $this->methodName === '__destruct');
        $return = $this->commentParser->getReturn();

        if ($isSpecialMethod === false && $methodName !== $className) {
            if ($return !== null) {
                $tagOrder = $this->commentParser->getTagOrders();
                $index = array_keys($tagOrder, 'return');
                $errorPos = ($commentStart + $return->getLine());
                $content = trim($return->getRawContent());

                if (count($index) > 1) {
                    $error = 'Only 1 @return tag is allowed in function comment';
                    $this->currentFile->addError($error, $errorPos, 'DuplicateReturn');

                    return;
                }

                $since = array_keys($tagOrder, 'since');
                if (count($since) === 1 && $this->tagIndex !== 0) {
                    $this->tagIndex++;
                    if ($index[0] !== $this->tagIndex) {
                        $error = 'The @return tag is in the wrong order; the tag follows @see (if used)';
                        $this->currentFile->addError($error, $errorPos, 'ReturnOrder');
                    }
                }

                if (empty($content) === true) {
                    $error = 'Return type missing for @return tag in function comment';
                    $this->currentFile->addError($error, $errorPos, 'MissingReturnType');
                } else {
                    // Check return type (can be multiple, separated by '|').
                    $typeNames = explode('|', $content);
                    $suggestedNames = [];
                    foreach ($typeNames as $i => $typeName) {
                        $suggestedName = PHP_CodeSniffer::suggestType($typeName);
                        if ($suggestedName === 'boolean') {
                            $suggestedName = 'bool';
                        } elseif ($suggestedName === 'integer') {
                            $suggestedName = 'int';
                        }
                        if (in_array($suggestedName, $suggestedNames) === false) {
                            $suggestedNames[] = $suggestedName;
                        }
                    }

                    $suggestedType = implode('|', $suggestedNames);
                    if ($content !== $suggestedType) {
                        $error = 'Function return type "%s" is invalid';
                        $data = [$content];
                        $this->currentFile->addError($error, $errorPos, 'InvalidReturn', $data);
                    }

                    $tokens = $this->currentFile->getTokens();

                    // If the return type is void, make sure there is
                    // no return statement in the function.
                    if ($content === 'void') {
                        if (isset($tokens[$this->functionToken]['scope_closer']) === true) {
                            $endToken = $tokens[$this->functionToken]['scope_closer'];

                            $tokens = $this->currentFile->getTokens();
                            for ($returnToken = $this->functionToken; $returnToken < $endToken; $returnToken++) {
                                if ($tokens[$returnToken]['code'] === T_CLOSURE) {
                                    $returnToken = $tokens[$returnToken]['scope_closer'];
                                    continue;
                                }

                                if ($tokens[$returnToken]['code'] === T_RETURN) {
                                    break;
                                }
                            }

                            if ($returnToken !== $endToken) {
                                // If the function is not returning anything, just
                                // exiting, then there is no problem.
                                $semicolon = $this->currentFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
                                if ($tokens[$semicolon]['code'] !== T_SEMICOLON) {
                                    $error = 'Function return type is void, but function contains return statement';
                                    $this->currentFile->addError($error, $errorPos, 'InvalidReturnVoid');
                                }
                            }
                        }
                    } elseif ($content !== 'mixed') {
                        // If return type is not void, there needs to be a
                        // returns statement somewhere in the function that
                        // returns something.
                        if (isset($tokens[$this->functionToken]['scope_closer']) === true) {
                            $endToken = $tokens[$this->functionToken]['scope_closer'];
                            $returnToken = $this->currentFile->findNext(T_RETURN, $this->functionToken, $endToken);
                            if ($returnToken === false) {
                                $error = 'Function return type is not void, but function has no return statement';
                                $this->currentFile->addError($error, $errorPos, 'InvalidNoReturn');
                            } else {
                                $semicolon = $this->currentFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
                                if ($tokens[$semicolon]['code'] === T_SEMICOLON) {
                                    $error = 'Function return type is not void, but function is returning void here';
                                    $this->currentFile->addError($error, $returnToken, 'InvalidReturnNotVoid');
                                }
                            }
                        }
                    }

                    $spacing = substr_count($return->getWhitespaceBeforeValue(), ' ');
                    if ($spacing !== 1) {
                        $error = '@return tag indented incorrectly; expected 1 space but found %s';
                        $data = [$spacing];
                        $this->currentFile->addError($error, $errorPos, 'ReturnIndent', $data);
                    }
                }
            } else {
                $tokens = $this->currentFile->getTokens();

                if (isset($tokens[$this->functionToken]['scope_closer']) === true) {
                    $endToken = $tokens[$this->functionToken]['scope_closer'];

                    $tokens = $this->currentFile->getTokens();
                    for ($returnToken = $this->functionToken; $returnToken < $endToken; $returnToken++) {
                        if ($tokens[$returnToken]['code'] === T_CLOSURE) {
                            $returnToken = $tokens[$returnToken]['scope_closer'];
                            continue;
                        }

                        if ($tokens[$returnToken]['code'] === T_RETURN) {
                            break;
                        }
                    }

                    if ($returnToken !== $endToken) {
                        // If the function is not returning anything, just
                        // exiting, then there is no problem.
                        $semicolon = $this->currentFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
                        if ($tokens[$semicolon]['code'] !== T_SEMICOLON && $tokens[$semicolon]['code'] !== T_NULL) {
                            $error = 'Function contains return statement, but no @return tag defined';
                            $this->currentFile->addError($error, $returnToken, 'InvalidReturnVoid');
                        }
                    }
                }
            }
        } else {
            // No return tag for constructor and destructor.
            if ($return !== null) {
                $errorPos = ($commentStart + $return->getLine());
                $error = '@return tag is not required for constructor and destructor';
                $this->currentFile->addError($error, $errorPos, 'ReturnNotRequired');
            }
        }
    }

    /**
     * Process any throw tags that this function comment has.
     *
     * @param int $commentStart The position in the stack where the comment started.
     *
     * @return void
     */
    protected function processThrows($commentStart)
    {
        if (count($this->commentParser->getThrows()) === 0) {
            return;
        }

        $tagOrder = $this->commentParser->getTagOrders();
        $index = array_keys($this->commentParser->getTagOrders(), 'throws');

        /** @var PHP_CodeSniffer_CommentParser_PairElement $throw */
        foreach ($this->commentParser->getThrows() as $i => $throw) {
            $exception = $throw->getValue();
            $errorPos = ($commentStart + $throw->getLine());
            if (empty($exception) === true) {
                $error = 'Exception type and comment missing for @throws tag in function comment';
                $this->currentFile->addError($error, $errorPos, 'InvalidThrows');
            }

            $since = array_keys($tagOrder, 'since');
            if (count($since) === 1 && $this->tagIndex !== 0) {
                $this->tagIndex++;
                if ($index[$i] !== $this->tagIndex) {
                    $error = 'The @throws tag is in the wrong order; the tag follows @return';
                    $this->currentFile->addError($error, $errorPos, 'ThrowsOrder');
                }
            }
        }
    }

    /**
     * Process the function parameter comments.
     *
     * @param int $commentStart The position in the stack where
     *                          the comment started.
     * @param int $commentEnd   The position in the stack where
     *                          the comment ended.
     *
     * @return void
     */
    protected function processParams($commentStart, $commentEnd)
    {
        $realParams = $this->currentFile->getMethodParameters($this->functionToken);
        /** @var PHP_CodeSniffer_CommentParser_ParameterElement[] $params */
        $params = $this->commentParser->getParams();
        $foundParams = [];

        if (empty($params) === false) {
            if (substr_count($params[(count($params) - 1)]->getWhitespaceAfter(), $this->currentFile->eolChar) !== 2) {
                $error = 'Last parameter comment requires a blank newline after it';
                $errorPos = ($params[(count($params) - 1)]->getLine() + $commentStart);
                $this->currentFile->addError($error, $errorPos, 'SpacingAfterParams');
            }

            // Parameters must appear immediately after the comment.
            if ($params[0]->getOrder() !== 2) {
                $error = 'Parameters must appear immediately after the comment';
                $errorPos = ($params[0]->getLine() + $commentStart);
                $this->currentFile->addError($error, $errorPos, 'SpacingBeforeParams');
            }

            /** @var PHP_CodeSniffer_CommentParser_ParameterElement $previousParam */
            $previousParam = null;
            $spaceBeforeVar = 10000;
            $spaceBeforeComment = 10000;
            $longestType = 0;
            $longestVar = 0;

            foreach ($params as $param) {
                $paramComment = trim($param->getComment());
                $errorPos = ($param->getLine() + $commentStart);

                // Make sure that there is only one space before the var type.
                if ($param->getWhitespaceBeforeType() !== ' ') {
                    $error = 'Expected 1 space before variable type';
                    $this->currentFile->addError($error, $errorPos, 'SpacingBeforeParamType');
                }

                $spaceCount = substr_count($param->getWhitespaceBeforeVarName(), ' ');
                if ($spaceCount < $spaceBeforeVar) {
                    $spaceBeforeVar = $spaceCount;
                    $longestType = $errorPos;
                }

                $spaceCount = substr_count($param->getWhitespaceBeforeComment(), ' ');

                if ($spaceCount < $spaceBeforeComment && $paramComment !== '') {
                    $spaceBeforeComment = $spaceCount;
                    $longestVar = $errorPos;
                }

                // Make sure they are in the correct order, and have the correct name.
                $pos = $param->getPosition();
                $paramName = ($param->getVarName() !== '') ? $param->getVarName() : '[ UNKNOWN ]';

                if ($previousParam !== null) {
                    $previousName = ($previousParam->getVarName() !== '') ? $previousParam->getVarName() : 'UNKNOWN';

                    // Check to see if the parameters align properly.
                    if ($param->alignsVariableWith($previousParam) === false) {
                        $error = 'The variable names for parameters %s (%s) and %s (%s) do not align';
                        $data = [
                            $previousName,
                            ($pos - 1),
                            $paramName,
                            $pos,
                        ];
                        $this->currentFile->addError($error, $errorPos, 'ParameterNamesNotAligned', $data);
                    }

                    if ($param->alignsCommentWith($previousParam) === false) {
                        $error = 'The comments for parameters %s (%s) and %s (%s) do not align';
                        $data = [
                            $previousName,
                            ($pos - 1),
                            $paramName,
                            $pos,
                        ];
                        $this->currentFile->addError($error, $errorPos, 'ParameterCommentsNotAligned', $data);
                    }
                }

                // Variable must be one of the supported standard type.
                $typeNames = explode('|', $param->getType());
                foreach ($typeNames as $typeName) {
                    $suggestedName = PHP_CodeSniffer::suggestType($typeName);
                    if ($suggestedName === 'boolean') {
                        $suggestedName = 'bool';
                    } elseif ($suggestedName === 'integer') {
                        $suggestedName = 'int';
                    }
                    if ($typeName !== $suggestedName) {
                        $error = 'Expected "%s"; found "%s" for %s at position %s';
                        $data = [
                            $suggestedName,
                            $typeName,
                            $paramName,
                            $pos,
                        ];
                        $this->currentFile->addError($error, $errorPos, 'IncorrectParamVarName', $data);
                    } elseif (count($typeNames) === 1) {
                        // Check type hint for array and custom type.
                        $suggestedTypeHint = '';
                        if (strpos($suggestedName, 'array') !== false || strpos($suggestedName, '[]') !== false) {
                            $suggestedTypeHint = 'array';
                        } elseif (strpos($suggestedName, 'callable') !== false) {
                            $suggestedTypeHint = 'callable';
                        } elseif (in_array($typeName, PHP_CodeSniffer::$allowedTypes) === false) {
                            $suggestedTypeHint = $suggestedName;
                        }

                        if ($suggestedTypeHint !== '' && isset($realParams[($pos - 1)]) === true) {
                            $typeHint = $realParams[($pos - 1)]['type_hint'];
                            if ($typeHint !== '' && $typeHint !== $suggestedTypeHint) {
                                $error = 'Expected type hint "%s"; found "%s" for %s at position %s';
                                $data = [
                                    $suggestedTypeHint,
                                    $typeHint,
                                    $paramName,
                                    $pos,
                                ];
                                $this->currentFile->addError($error, ($commentEnd + 2), 'IncorrectTypeHint', $data);
                            }
                        } elseif ($suggestedTypeHint === '' && isset($realParams[($pos - 1)]) === true) {
                            $typeHint = $realParams[($pos - 1)]['type_hint'];
                            if ($typeHint !== '') {
                                $error = 'Unknown type hint "%s" found for %s at position %s';
                                $data = [
                                    $typeHint,
                                    $paramName,
                                    $pos,
                                ];
                                $this->currentFile->addError($error, ($commentEnd + 2), 'InvalidTypeHint', $data);
                            }
                        }
                    }
                }

                // Make sure the names of the parameter comment matches the
                // actual parameter.
                if (isset($realParams[($pos - 1)]) === true) {
                    $realName = $realParams[($pos - 1)]['name'];
                    $foundParams[] = $realName;

                    if ($realName !== $paramName) {
                        $code = 'ParamNameNoMatch';
                        $data = [
                            $paramName,
                            $realName,
                            $pos,
                        ];

                        $error = 'Doc comment for var %s does not match ';
                        if (strtolower($paramName) === strtolower($realName)) {
                            $error .= 'case of ';
                            $code = 'ParamNameNoCaseMatch';
                        }

                        $error .= 'actual variable name %s at position %s';

                        $this->currentFile->addError($error, $errorPos, $code, $data);
                    }
                } elseif (substr($paramName, -4) !== ',...') {
                    // We must have an extra parameter comment.
                    $error = 'Superfluous doc comment at position ' . $pos;
                    $this->currentFile->addError($error, $errorPos, 'ExtraParamComment');
                }

                if ($param->getVarName() === '') {
                    $error = 'Missing parameter name at position ' . $pos;
                    $this->currentFile->addError($error, $errorPos, 'MissingParamName');
                }

                if ($param->getType() === '') {
                    $error = 'Missing type at position ' . $pos;
                    $this->currentFile->addError($error, $errorPos, 'MissingParamType');
                }

                if ($paramComment !== '') {
                    // Param comments must start with a capital letter and
                    // end with the full stop.
                    $firstChar = $paramComment{0};
                    if (preg_match('|\p{Lu}|u', $firstChar) === 0) {
                        $error = 'Param comment must start with a capital letter';
                        $this->currentFile->addError($error, $errorPos, 'ParamCommentNotCapital');
                    }

                    $lastChar = $paramComment[(strlen($paramComment) - 1)];
                    if ($lastChar !== '.') {
                        $error = 'Param comment must end with a full stop';
                        $this->currentFile->addError($error, $errorPos, 'ParamCommentFullStop');
                    }
                }

                $previousParam = $param;
            }

            if ($spaceBeforeVar !== 1 && $spaceBeforeVar !== 10000) {
                $error = 'Expected 1 space after the longest type';
                $this->currentFile->addError($error, $longestType, 'SpacingAfterLongType');
            }

            if ($spaceBeforeComment !== 1 && $spaceBeforeComment !== 10000) {
                $error = 'Expected 1 space after the longest variable name';
                $this->currentFile->addError($error, $longestVar, 'SpacingAfterLongName');
            }
        }

        $realNames = [];
        foreach ($realParams as $realParam) {
            $realNames[] = $realParam['name'];
        }

        // Report missing comments.
        $diff = array_diff($realNames, $foundParams);
        foreach ($diff as $neededParam) {
            if (count($params) !== 0) {
                $errorPos = ($params[(count($params) - 1)]->getLine() + $commentStart);
            } else {
                $errorPos = $commentStart;
            }

            $error = 'Doc comment for "%s" missing';
            $data = [$neededParam];
            $this->currentFile->addError($error, $errorPos, 'MissingParamTag', $data);
        }
    }

    /**
     * Process a list of unknown tags.
     *
     * @param int $commentStart The position in the stack where the comment started.
     * @param int $commentEnd   The position in the stack where the comment ended.
     *
     * @return void
     */
    protected function processUnknownTags($commentStart, $commentEnd)
    {
    }

    /**
     * @param string $content
     *
     * @return bool
     */
    private static function isFunctionIgnored($content)
    {
        static $ignored = [
            '__toString',
        ];

        return in_array($content, $ignored, true);
    }

    /**
     * Checks for extra whitespaces in given tokens.
     *
     * @param array $array_slice
     */
    private function checkForExtraWhiteSpace($array_slice)
    {
        foreach ($array_slice as $stackPtr => $token) {
            $content = str_replace(["\n", "\r"], '', $token['content']);
            if ($content !== rtrim($content)) {
                $this->currentFile->addError(
                    'Whitespace found at end of line',
                    $stackPtr,
                    'EndLineWhiteSpace'
                );
            }
        }
    }
}
