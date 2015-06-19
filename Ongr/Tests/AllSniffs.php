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
        $baseDir = pathinfo(getcwd()."/Ongr", PATHINFO_DIRNAME);

        \PHP_CodeSniffer::setConfigData('installed_paths', $baseDir);
        $path = pathinfo(\PHP_CodeSniffer::getInstalledStandardPath('Ongr'), PATHINFO_DIRNAME);
        $testsDir = $path . DIRECTORY_SEPARATOR . 'Tests' . DIRECTORY_SEPARATOR . 'Unit';

        $directoryIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testsDir));

        /** @var \SplFileInfo $fileinfo */
        foreach ($directoryIterator as $file) {
            // Skip hidden and extension must be php.
            if ($file->getFilename()[0] === '.' || pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            $className = str_replace(
                [$baseDir . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR],
                ['', '\\'],
                substr($file, 0, -4)
            );

            $suite->addTest(new $className('getErrorList'));
        }

        return $suite;
    }
}
