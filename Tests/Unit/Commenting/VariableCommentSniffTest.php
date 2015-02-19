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
            22 => ['Class field docs should not contain field name'],
            27 => ['Class field docs should not contain field name'],
            42 => ['Class field docs should not contain field name'],
            47 => ['Class field docs should not contain field name'],
            57 => ['Expected "array(string => array)"; found "array(string => array())'],
            62 => ['Variable comments must end in full-stops, exclamation marks, or question marks'],
            78 => ['Only 1 @var tag is allowed in variable comment'],
            // Multiple declarations not allowed but doc check for second variable still fires and fails.
            80 => ['Missing variable doc comment'],
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
