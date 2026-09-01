<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListStateLibraryBuilder;

final class QueryListStateLibraryBuilderTest extends TestCase
{
    public function testBuildsStateEntriesInHistoricalOrder(): void
    {
        $entries = (new QueryListStateLibraryBuilder())->build('qcode();', "\n");

        self::assertSame(
            [
                'ff_queryCurrPage',
                'ff_queryPageSize',
                'ff_queryCols',
                'ff_queryCheckbox',
                'ff_queryHeader',
                'ff_queryPagenav',
                'ff_queryRows',
            ],
            array_column($entries, 0)
        );
        self::assertSame("var ff_queryRows = new Array();\nqcode();", $entries[6][1]);
    }
}
