<?php

/**
 * An abstract class that all sniff unit tests must extend.
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

namespace ONGR\Tests;

use DirectoryIterator;
use Exception;
use PHPUnit_Framework_TestCase;
use PHP_CodeSniffer;
use PHPUnit_Framework_Error;
use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Exception;

/**
 * An abstract class that all sniff unit tests must extend.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings that are not found, or
 * warnings and errors that are not expected, are considered test failures.
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
abstract class AbstractSniffUnitTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHP_CodeSniffer The PHP_CodeSniffer object used for testing.
     */
    protected static $phpcs = null;

    /**
     * Sets up this unit test.
     *
     * @return void
     */
    protected function setUp()
    {
        if (self::$phpcs === null) {
            self::$phpcs = new PHP_CodeSniffer();
        }
    }

    /**
     * Should this test be skipped for some reason.
     *
     * @return bool
     */
    protected function shouldSkipTest()
    {
        return false;
    }

    /**
     * Tests the extending classes Sniff class.
     *
     * @return void
     * @throws PHPUnit_Framework_Error
     */
    final protected function runTest()
    {
        // Skip this test if we can't run in this environment.
        if ($this->shouldSkipTest() === true) {
            $this->markTestSkipped();
        }

        // The basis for determining file locations.
        $basename = substr(get_class($this), 0, -4);

        // The code of the sniff we are testing.
        $parts = explode('\\', $basename);
        $sniffCode = $parts[0] . '.' . $parts[3] . '.' . substr($parts[4], 0, -5);

        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'Unit' . DIRECTORY_SEPARATOR . $parts[3];
        $testFileBase = $dir . DIRECTORY_SEPARATOR . $parts[4] . 'Test';

        // Get a list of all test files to check. These will have the same base
        // name but different extensions. We ignore the .php file as it is the class.
        $testFiles = [];

        $di = new DirectoryIterator($dir);

        foreach ($di as $file) {
            $path = $file->getPathname();
            if (substr($path, 0, strlen($testFileBase)) === $testFileBase) {
                if ($path !== $testFileBase . '.php' && substr($path, -5) !== 'fixed') {
                    $testFiles[] = $path;
                }
            }
        }

        // Get them in order.
        sort($testFiles);
        self::$phpcs->process([], '.', [$sniffCode]);
        self::$phpcs->setIgnorePatterns([]);

        $failureMessages = [];
        foreach ($testFiles as $testFile) {
            $phpcsFile = null;
            try {
                $phpcsFile = self::$phpcs->processFile($testFile);
            } catch (Exception $e) {
                $this->fail('An unexpected exception has been caught: ' . $e->getMessage());
            }

            $failures = $this->generateFailureMessages($phpcsFile);
            $failureMessages = array_merge($failureMessages, $failures);
        }

        if (empty($failureMessages) === false) {
            $this->fail(implode(PHP_EOL, $failureMessages));
        }
    }

    /**
     * Generate a list of test failures for a given sniffed file.
     *
     * @param PHP_CodeSniffer_File $file The file being tested.
     *
     * @return array
     * @throws PHP_CodeSniffer_Exception
     */
    public function generateFailureMessages(PHP_CodeSniffer_File $file)
    {
        $testFile = $file->getFilename();

        $foundErrors = $file->getErrors();
        $foundWarnings = $file->getWarnings();
        $expectedErrors = $this->getErrorList(basename($testFile));
        $expectedWarnings = $this->getWarningList(basename($testFile));

        if (is_array($expectedErrors) === false) {
            throw new PHP_CodeSniffer_Exception('getErrorList() must return an array');
        }

        if (is_array($expectedWarnings) === false) {
            throw new PHP_CodeSniffer_Exception('getWarningList() must return an array');
        }

        /*
         * We merge errors and warnings together to make it easier
         * to iterate over them and produce the errors string. In this way,
         * we can report on errors and warnings in the same line even though
         * it's not really structured to allow that.
         */

        $allProblems = [];
        $failureMessages = [];

        foreach ($foundErrors as $line => $lineErrors) {
            foreach ($lineErrors as $column => $errors) {
                if (isset($allProblems[$line]) === false) {
                    $allProblems[$line] = [
                        'expected_errors' => [],
                        'expected_warnings' => [],
                        'found_errors' => [],
                        'found_warnings' => [],
                    ];
                }

                $foundErrorsTemp = [];
                foreach ($allProblems[$line]['found_errors'] as $foundError) {
                    $foundErrorsTemp[] = $foundError;
                }

                $errorsTemp = [];
                foreach ($errors as $foundError) {
                    $errorsTemp[] = $foundError['message'] . ' (' . $foundError['source'] . ')';
                }

                $allProblems[$line]['found_errors'] = array_merge($foundErrorsTemp, $errorsTemp);
            }

            if (isset($expectedErrors[$line]) === true) {
                $allProblems[$line]['expected_errors'] = $expectedErrors[$line];
            } else {
                $allProblems[$line]['expected_errors'] = [];
            }

            unset($expectedErrors[$line]);
        }

        foreach ($expectedErrors as $line => $numErrors) {
            if (isset($allProblems[$line]) === false) {
                $allProblems[$line] = [
                    'expected_errors' => [],
                    'expected_warnings' => [],
                    'found_errors' => [],
                    'found_warnings' => [],
                ];
            }

            $allProblems[$line]['expected_errors'] = $numErrors;
        }

        foreach ($foundWarnings as $line => $lineWarnings) {
            foreach ($lineWarnings as $column => $warnings) {
                if (isset($allProblems[$line]) === false) {
                    $allProblems[$line] = [
                        'expected_errors' => [],
                        'expected_warnings' => [],
                        'found_errors' => [],
                        'found_warnings' => [],
                    ];
                }

                $foundWarningsTemp = [];
                foreach ($allProblems[$line]['found_warnings'] as $foundWarning) {
                    $foundWarningsTemp[] = $foundWarning;
                }

                $warningsTemp = [];
                foreach ($warnings as $warning) {
                    $warningsTemp[] = $warning['message'] . ' (' . $warning['source'] . ')';
                }

                $allProblems[$line]['found_warnings'] = array_merge($foundWarningsTemp, $warningsTemp);
            }

            if (isset($expectedWarnings[$line]) === true) {
                $allProblems[$line]['expected_warnings'] = $expectedWarnings[$line];
            } else {
                $allProblems[$line]['expected_warnings'] = [];
            }

            unset($expectedWarnings[$line]);
        }

        foreach ($expectedWarnings as $line => $numWarnings) {
            if (isset($allProblems[$line]) === false) {
                $allProblems[$line] = [
                    'expected_errors' => [],
                    'expected_warnings' => [],
                    'found_errors' => [],
                    'found_warnings' => [],
                ];
            }

            $allProblems[$line]['expected_warnings'] = $numWarnings;
        }

        // Order the messages by line number.
        ksort($allProblems);

        foreach ($allProblems as $line => $problems) {
            $failureMessages = array_merge(
                $failureMessages,
                $this->processProblems(
                    $problems['expected_errors'],
                    $problems['found_errors'],
                    $line,
                    $testFile,
                    'error'
                ),
                $this->processProblems(
                    $problems['expected_warnings'],
                    $problems['found_warnings'],
                    $line,
                    $testFile,
                    'warning'
                )
            );
        }

        return $failureMessages;
    }

    /**
     * Checks if all found problems were expected.
     *
     * @param array  $expected
     * @param array  $found
     * @param int    $line
     * @param string $testFile
     * @param string $type
     *
     * @return array
     */
    private function processProblems($expected, $found, $line, $testFile, $type)
    {
        $messages = [];
        $file = basename($testFile);

        foreach ($expected as $messageKey => $message) {
            foreach ($found as $errorKey => $error) {
                if (strpos($error, $message) === 0) {
                    unset($expected[$errorKey]);
                    unset($found[$messageKey]);
                    break;
                }
            }
        }

        foreach ($expected as $message) {
            $messages[] = "[LINE {$line}] Expected {$type} \"$message\" in {$file} not found";
        }
        foreach ($found as $message) {
            $messages[] = "[LINE {$line}] Unexpected {$type} \"$message\" in {$file} found";
        }

        return $messages;
    }

    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent array of expected error messages.
     *
     * @return array
     */
    abstract protected function getErrorList();

    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent array of expected warning messages.
     *
     * @return array
     */
    abstract protected function getWarningList();
}
