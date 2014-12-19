<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\Tests\Unit\Commenting;

use ONGR\Tests\AbstractSniffUnitTest;

/**
 * FunctionCommentSniff class.
 */
class FunctionCommentSniffTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritdoc}
     */
    protected function getErrorList()
    {
        return [
            20 => ['Whitespace found at end of line'],
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
