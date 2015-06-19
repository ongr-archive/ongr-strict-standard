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
 * VariableCommentSniffTest class.
 */
class DocCommentAlignmentSniffTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritdoc}
     */
    protected function getErrorList()
    {
        return [
            43 => 1,
            54 => 1,
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
