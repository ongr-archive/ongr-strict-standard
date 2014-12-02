<?php
/**
 * ONGR_Sniffs_Debug_JSLintSniff.
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

namespace ONGR\Sniffs\Debug;

use PHP_CodeSniffer;
use PHP_CodeSniffer_Exception;
use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * ONGR_Sniffs_Debug_JSLintSniff.
 *
 * Runs jslint.js on the file.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class JSLintSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @var array A list of tokenizers this sniff supports.
     */
    public $supportedTokenizers = ['JS'];

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return int[]
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }//end register()

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     * @throws PHP_CodeSniffer_Exception If jslint.js could not be run
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $fileName = $phpcsFile->getFilename();

        $rhinoPath = PHP_CodeSniffer::getConfigData('rhino_path');
        $jslintPath = PHP_CodeSniffer::getConfigData('jslint_path');
        if ($rhinoPath === null || $jslintPath === null) {
            return;
        }

        $cmd = "$rhinoPath \"$jslintPath\" \"$fileName\"";
        exec($cmd, $output, $retval);

        if (is_array($output) === true) {
            $tokens = $phpcsFile->getTokens();

            foreach ($output as $finding) {
                $matches = [];
                $numMatches = preg_match('/Lint at line ([0-9]+).*:(.*)$/', $finding, $matches);
                if ($numMatches === 0) {
                    continue;
                }

                $line = (int)$matches[1];
                $message = 'jslint says: ' . trim($matches[2]);

                // Find the token at the start of the line.
                $lineToken = null;
                foreach ($tokens as $ptr => $info) {
                    if ($info['line'] === $line) {
                        $lineToken = $ptr;
                        break;
                    }
                }

                if ($lineToken !== null) {
                    $phpcsFile->addWarning($message, $lineToken, 'ExternalTool');
                }
            }//end foreach
        }//end if
    }//end process()
}
