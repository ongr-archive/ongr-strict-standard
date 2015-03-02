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
class VariableCommentSniffTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritdoc}
     */
    protected function getErrorList()
    {
        return [
            22 => 1,
            27 => 1,
            42 => 1,
            47 => 1,
            57 => 1,
            62 => 1,
            77 => 1,
            78 => 1,
            // Multiple declarations not allowed but doc check for second variable still fires and fails.
            80 => 1,
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
