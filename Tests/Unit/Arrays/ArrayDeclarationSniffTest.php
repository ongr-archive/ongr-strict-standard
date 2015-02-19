<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ongr\Tests\Unit\Arrays;

use Ongr\Tests\AbstractSniffUnitTest;

/**
 * ArrayDeclarationSniffTest class.
 */
class ArrayDeclarationSniffTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritdoc}
     */
    protected function getErrorList()
    {
        return [
            3 => ['Empty array declaration must have no space between the parentheses'],
            4 => ['Must use short array syntax for arrays'],
            22 => ['Each line in an array declaration must end with a comma'],
            26 => ['Each line in an array declaration must end with a comma'],
            30 => ['Each line in an array declaration must end with a comma'],
            37 => [
                'Expected 1 space between "1" and double arrow; 0 found',
                'Expected 1 space between double arrow and "1"; 0 found',
            ],
            39 => ['Expected 1 space between double arrow and "1"; 0 found'],
            41 => ['Expected 1 space between "1" and double arrow; 0 found'],
            43 => ['Expected 1 space between double arrow and "1"; 2 found'],
            45 => ['Expected 1 space between "1" and double arrow; 2 found'],
            48 => ['Each line in an array declaration must end with a comma'],
            57 => ['Key specified for array entry; first entry has no key'],
            62 => ['No key specified for array entry; first entry specifies key'],
            67 => ['Expected single whitespace before double arrow, found 2 spaces'],
            81 => ['Array key not aligned correctly; expected 4 spaces but found 12'],
            82 => ['Array key not aligned correctly; expected 4 spaces but found 12'],
            83 => ['Closing parenthesis not aligned correctly; expected 1 space(s) but found 9'],
            85 => [
                'Comma not allowed after last value in single-line array declaration',
                'Expected 1 space between comma and "2"; 0 found',
                'Expected 1 space between comma and "3"; 0 found',
                'Expected 1 space between comma and "4"; 0 found',
            ],
            86 => ['Comma not allowed after last value in single-line array declaration'],
            87 => ['Comma not allowed after last value in single-line array declaration'],
            88 => ['Comma not allowed after last value in single-line array declaration'],
            89 => ['Expected 1 space between comma and "2"; 2 found'],
            90 => ['Expected 0 spaces between "1" and comma; 1 found'],
            91 => [
                'Expected 1 space between comma and "2"; 0 found',
                'Expected 0 spaces between "1" and comma; 1 found',
            ],
            101 => [
                'Closing parenthesis of array declaration must be on a new line',
                'Comma required after last value in array declaration',
            ],
            104 => ['Closing parenthesis of array declaration must be on a new line'],
            107 => ['Expected 0 spaces between "2" and comma; 1 found'],
            113 => ['Expected 0 spaces between "2" and comma; 1 found'],
            116 => ['The first value in a multi-value array must be on a new line'],
            119 => ['The first value in a multi-value array must be on a new line'],
            124 => ['Expected single whitespace before double arrow, found 0 spaces'],
            129 => ['Array value not aligned correctly; expected 5 spaces but found 6'],
            132 => ['The first index in a multi-value array must be on a new line'],
            135 => ['The first index in a multi-value array must be on a new line'],
            137 => ['The first index in a multi-value array must be on a new line'],
            148 => ['Expected 0 spaces between ")" and comma; 1 found'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getWarningList()
    {
        return [];
    }
}
