<?php

/**
 * A test class for testing all sniffs for installed standards.
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

namespace Ongr\Tests;

use PHPUnit_TextUI_TestRunner;
use PHPUnit_Framework_TestSuite;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * A test class for testing all sniffs for installed standards.
 *
 * Usage: phpunit AllSniffs.php
 *
 * This test class loads all unit tests for all installed standards into a
 * single test suite and runs them. Errors are reported on the command line.
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
class AllSniffs
{
    /**
     * Prepare the test runner.
     *
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Add all sniff unit tests into a test suite.
     *
     * Sniff unit tests are found by recursing through the 'Tests' directory
     * of each installed coding standard.
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHP CodeSniffer Standards');

        $standardDir = __DIR__ . DIRECTORY_SEPARATOR . 'Unit';

        // Locate the actual directory that contains the standard's tests.
        // This is individual to each standard as they could be symlinked in.
        $baseDir = dirname(dirname(dirname($standardDir)));

        $di = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($standardDir));

        foreach ($di as $file) {
            // Skip hidden files.
            if (substr($file->getFilename(), 0, 1) === '.') {
                continue;
            }

            // Tests must have the extension 'php'.
            $parts = explode('.', $file);
            $ext = array_pop($parts);
            if ($ext !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            $className = str_replace([$baseDir . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], ['', '\\'], $filePath);
            $className = substr($className, 0, -4);

            $class = new $className('getErrorList');
            $suite->addTest($class);
        }

        return $suite;
    }
}
