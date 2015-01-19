<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\Tests\Unit\PHP;

use ONGR\Tests\AbstractSniffUnitTest;

/**
 * DisallowMultipleAssignmentsSniffTest class.
 */
class DisallowMultipleAssignmentsSniffTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritdoc}
     */
    protected function getErrorList()
    {
        return [
            20 => ['Assignments must be the first block of code on a line'],
            24 => ['Assignments must be the first block of code on a line'],
            46 => ['Assignments must be the first block of code on a line'],
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
