<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ongr\Sniffs\Helper;

/**
 * Extracts useful information from doc comments.
 */
class Comment
{
    /**
     * @var int
     */
    private $commentStartLine;

    /**
     * @var int
     */
    private $commentEndLine;

    /**
     * @var int
     */
    private $shortDescriptionLine;

    /**
     * @var int
     */
    private $longDescriptionLine;

    /**
     * @var string
     */
    private $shortDescription = '';

    /**
     * @var string
     */
    private $longDescription = '';

    /**
     * @var int
     */
    private $shortDescriptionStartPtr;

    /**
     * @var int
     */
    private $shortDescriptionEndPtr;

    /**
     * @var int
     */
    private $longDescriptionStartPtr;

    /**
     * @var int
     */
    private $longDescriptionEndPtr;

    /**
     * @var int[]
     */
    private $junk = [];

    /**
     * @param \PHP_CodeSniffer_File $phpcsFile
     * @param int                   $commentStackPtr
     */
    public function __construct(\PHP_CodeSniffer_File $phpcsFile, $commentStackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $commentEnd = $tokens[$commentStackPtr]['comment_closer'];

        $this->commentStartLine = $tokens[$commentStackPtr]['line'];
        $this->commentEndLine = $tokens[$commentEnd]['line'];

        $line = $this->commentStartLine;

        $commentTokens = array_slice($tokens, $commentStackPtr + 1, $commentEnd - $commentStackPtr, true);
        $short = null;
        $description = true;
        $lastText = null;

        foreach ($commentTokens as $ptr => $token) {
            if ($token['line'] != $line) {
                // Start of new line.
                $description = false;
                $line = $token['line'];
                if ($short) {
                    // Short description consists of one line so end it.
                    $short = false;
                    $this->shortDescriptionEndPtr = $lastText;
                }
            }

            if ($token['type'] == 'T_DOC_COMMENT_STAR') {
                // Content after asterisk can be added to description.
                $description = true;
            }

            if ($token['type'] == 'T_DOC_COMMENT_STRING') {
                if (!$description) {
                    // Text found in odd location.
                    $this->junk[] = $ptr;
                    continue;
                }

                if ($short === null) {
                    // Text found must be short description.
                    $this->shortDescriptionStartPtr = $ptr;
                    $this->shortDescriptionLine = $token['line'];
                    $short = true;
                }
                if ($short) {
                    $this->shortDescription .= $token['content'];
                } else {
                    // Short description ended must be long description.
                    if (!$this->longDescriptionStartPtr) {
                        $this->longDescriptionStartPtr = $ptr;
                        $this->longDescriptionLine = $token['line'];
                    }
                    $this->longDescription .= $token['content'];
                }
                $lastText = $ptr;
            }

            if ($token['type'] == 'T_DOC_COMMENT_WHITESPACE' && $description) {
                // White spaces in descriptions.
                if ($short) {
                    $this->shortDescription .= $token['content'];
                } elseif ($this->longDescriptionStartPtr) {
                    $this->longDescription .= $token['content'];
                }
            }

            if ($token['type'] == 'T_DOC_COMMENT_TAG' || $token['type'] == 'T_DOC_COMMENT_CLOSE_TAG') {
                // No more descriptions after tags.
                if ($this->longDescriptionStartPtr) {
                    $this->longDescriptionEndPtr = $lastText;
                } elseif ($this->shortDescriptionStartPtr) {
                    $this->shortDescriptionEndPtr = $lastText;
                }
                break;
            }
        }
        $this->shortDescription = trim($this->shortDescription);
        $this->longDescription = trim($this->longDescription);
    }

    /**
     * @return int
     */
    public function getCommentStartLine()
    {
        return $this->commentStartLine;
    }

    /**
     * @return int
     */
    public function getCommentEndLine()
    {
        return $this->commentEndLine;
    }

    /**
     * @return int
     */
    public function getShortDescriptionLine()
    {
        return $this->shortDescriptionLine;
    }

    /**
     * @return int
     */
    public function getLongDescriptionLine()
    {
        return $this->longDescriptionLine;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @return string
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * @return int
     */
    public function getShortDescriptionStartPtr()
    {
        return $this->shortDescriptionStartPtr;
    }

    /**
     * @return int
     */
    public function getShortDescriptionEndPtr()
    {
        return $this->shortDescriptionEndPtr;
    }

    /**
     * @return int
     */
    public function getLongDescriptionStartPtr()
    {
        return $this->longDescriptionStartPtr;
    }

    /**
     * @return int
     */
    public function getLongDescriptionEndPtr()
    {
        return $this->longDescriptionEndPtr;
    }

    /**
     * Gets pointers to text where it is not supposed to be.
     *
     * @return int[]
     */
    public function getJunk()
    {
        return $this->junk;
    }
}
