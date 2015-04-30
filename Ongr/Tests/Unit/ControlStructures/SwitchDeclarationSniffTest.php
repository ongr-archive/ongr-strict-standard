<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ongr\Tests\Unit\ControlStructures;

use Ongr\Tests\AbstractSniffUnitTest;

/**
 * SwitchDeclarationSniffTest class.
 */
class SwitchDeclarationSniffTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritdoc}
     */
    protected function getErrorList()
    {
        return [
            3 => 1,
            10 => 1,
            17 => 1,
            24 => 0,
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
