<?php
/**
 * Parses and verifies the class doc comment.
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

use Ongr\Sniffs\Helper\Comment;

/**
 * Parses and verifies the class doc comment.
 *
 * Verifies that :
 * <ul>
 *  <li>A class doc comment exists.</li>
 *  <li>There is exactly one blank line before the class comment.</li>
 *  <li>There are no blank lines after the class comment.</li>
 *  <li>Short and long descriptions end with a full stop and start with capital letter.</li>
 *  <li>There is a blank line between descriptions.</li>
 * </ul>
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Ongr_Sniffs_Commenting_ClassCommentSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_CLASS);

    }//end register()


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

        $className = $phpcsFile->getDeclarationName($stackPtr);
        $isTestClass = preg_match('/Test$/', $className) === 1;

        $tokens = $phpcsFile->getTokens();
        $find   = PHP_CodeSniffer_Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT
        ) {
            if (!$isTestClass) {
                $phpcsFile->addError('Missing class doc comment', $stackPtr, 'Missing');
            }
            return;
        }

        // Try and determine if this is a file comment instead of a class comment.
        // We assume that if this is the first comment after the open PHP tag, then
        // it is most likely a file comment instead of a class comment.
        if ($tokens[$commentEnd]['code'] === T_DOC_COMMENT_CLOSE_TAG) {
            $start = ($tokens[$commentEnd]['comment_opener'] - 1);
        } else {
            $start = $phpcsFile->findPrevious(T_COMMENT, ($commentEnd - 1), null, true);
        }

        $prev = $phpcsFile->findPrevious(T_WHITESPACE, $start, null, true);
        if ($tokens[$prev]['code'] === T_OPEN_TAG) {
            $prevOpen = $phpcsFile->findPrevious(T_OPEN_TAG, ($prev - 1));
            if ($prevOpen === false) {
                // This is a comment directly after the first open tag,
                // so probably a file comment.
                if (!$isTestClass) {
                    $phpcsFile->addError('Missing class doc comment', $stackPtr, 'Missing');
                }
                return;
            }
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a class comment', $stackPtr, 'WrongStyle');
            return;
        }

        if ($tokens[$commentEnd]['line'] !== ($tokens[$stackPtr]['line'] - 1)) {
            $error = 'There must be no blank lines after the class comment';
            $phpcsFile->addError($error, $commentEnd, 'SpacingAfter');
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];
        if ($tokens[$prev]['line'] !== ($tokens[$commentStart]['line'] - 2)) {
            $error = 'There must be exactly one blank line before the class comment';
            $phpcsFile->addError($error, $commentStart, 'SpacingBefore');
        }

        //ONGR uses special tags which squiz not allow
//        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
//            $error = '%s tag is not allowed in class comment';
//            $data  = array($tokens[$tag]['content']);
//            $phpcsFile->addWarning($error, $tag, 'TagNotAllowed', $data);
//        }

        // ONGR Validate doc comment content.
        $comment = new Comment($phpcsFile, $commentStart);
        $punctuation = ['.', '!', '?'];

        $error = 'Text must be after asterisks';
        foreach ($comment->getJunk() as $ptr) {
            $phpcsFile->addError($error, $ptr, 'MissingAsterisk');
        }

        if (!$comment->getShortDescription()) {
            $error = 'Missing short description in class comment';
            $phpcsFile->addError($error, $commentStart, 'ShortDescription');

            return;
        }

        if ($comment->getCommentStartLine() + 1 !== $comment->getShortDescriptionLine()) {
            $error = 'Short description in class comment should be in second line';
            $phpcsFile->addError($error, $comment->getShortDescriptionStartPtr(), 'ShortDescriptionLine');
        }

        if (ucfirst($comment->getShortDescription()) !== $comment->getShortDescription()) {
            $error = 'Short description must start with capital letter';
            $fix = $phpcsFile->addFixableError($error, $comment->getShortDescriptionStartPtr(), 'ShortDescriptionCapital');
            if ($fix) {
                $ptr = $comment->getShortDescriptionStartPtr();
                $phpcsFile->fixer->replaceToken(
                    $ptr,
                    ucfirst($tokens[$ptr]['content'])
                );
            }
        }

        if (!in_array(substr($comment->getShortDescription(), -1), $punctuation, true)) {
            $error = 'Short description must end with punctuation';
            $fix = $phpcsFile->addFixableError($error, $comment->getShortDescriptionEndPtr(), 'ShortDescriptionPunctuation');
            if ($fix) {
                $ptr = $comment->getShortDescriptionEndPtr();
                $phpcsFile->fixer->replaceToken(
                    $ptr,
                    $tokens[$ptr]['content'] . '.'
                );
            }
        }

        if (!$comment->getLongDescription()) {
            return;
        }

        if ($comment->getLongDescriptionLine() !== $comment->getShortDescriptionLine() + 2) {
            $error = 'Long description must be separated by 1 blank line from short description';
            $phpcsFile->addError($error, $comment->getLongDescriptionStartPtr(), 'LongDescriptionLocation');
        }

        if (!in_array(substr($comment->getLongDescription(), -1), $punctuation, true)) {
            $error = 'Long description must end with punctuation';
            $fix = $phpcsFile->addFixableError($error, $comment->getLongDescriptionEndPtr(), 'LongDescriptionPunctuation');
            if ($fix) {
                $ptr = $comment->getLongDescriptionEndPtr();
                $phpcsFile->fixer->replaceToken(
                    $ptr,
                    $tokens[$ptr]['content'] . '.'
                );
            }
        }

        if (ucfirst($comment->getLongDescription()) !== $comment->getLongDescription()) {
            $error = 'Long description must start with capital letter';
            $fix = $phpcsFile->addFixableError(
                $error,
                $comment->getLongDescriptionStartPtr(),
                'LongDescriptionCapital'
            );

            if ($fix) {
                $ptr = $comment->getLongDescriptionStartPtr();
                $phpcsFile->fixer->replaceToken(
                    $ptr,
                    ucfirst($tokens[$ptr]['content'])
                );
            }
        }
    }//end process()


}//end class