<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ongr\Tests\Unit\Commenting;

use Ongr\Tests\AbstractSniffUnitTest;

/**
 * Class ClassCommentSniffTest.
 */
class ClassCommentSniffTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritdoc}
     */
    protected function getErrorList()
    {
        return [
            14 => 1,
            21 => 1,
            33 => 1,
            42 => 1,
            50 => 1,
            62 => 1,
            85 => 1,
            92 => 1,
            93 => 1,
            95 => 1,
            96 => 1,
            97 => 1,
            103 => 1,
            108 => 1,
            115 => 1,
            122 => 2,
            131 => 1,
            140 => 1,
            149 => 2,
            158 => 1,
            159 => 1,
            168 => 1,
            179 => 1,
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
