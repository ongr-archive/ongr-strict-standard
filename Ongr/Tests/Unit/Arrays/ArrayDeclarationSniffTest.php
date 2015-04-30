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
            3 => 1,
            4 => 1,
            22 => 1,
            26 => 1,
            30 => 1,
            37 => 2,
            39 => 1,
            41 => 1,
            43 => 1,
            45 => 1,
            48 => 1,
            57 => 1,
            62 => 1,
            67 => 1,
            81 => 1,
            82 => 1,
            83 => 1,
            85 => 4,
            86 => 1,
            87 => 1,
            88 => 1,
            89 => 1,
            90 => 1,
            91 => 2,
            101 => 2,
            104 => 1,
            107 => 1,
            113 => 1,
            116 => 1,
            119 => 1,
            124 => 1,
            129 => 1,
            132 => 1,
            135 => 1,
            137 => 1,
            148 => 1,
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
