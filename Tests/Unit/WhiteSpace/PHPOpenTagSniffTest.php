<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ongr\Tests\Unit\WhiteSpace;

use Ongr\Tests\AbstractSniffUnitTest;

/**
 * PHPOpenTagSniffTest class.
 */
class PHPOpenTagSniffTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritdoc}
     */
    protected function getErrorList()
    {
        return [
            1 => ['There must be one blank line after the php open tag and no whitespaces'],
            3 => ['There must be one blank line after the php open tag and no whitespaces'],
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
