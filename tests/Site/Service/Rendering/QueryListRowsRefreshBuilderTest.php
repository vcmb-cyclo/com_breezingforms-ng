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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListRowsRefreshBuilder;

final class QueryListRowsRefreshBuilderTest extends TestCase
{
    public function testBuildsPagedRowRefreshLogic(): void
    {
        $script = (new QueryListRowsRefreshBuilder())->build("\n");

        self::assertStringContainsString('var qrows = ff_queryRows[id];', $script);
        self::assertStringContainsString('var lastpage = 1;', $script);
        self::assertStringContainsString('row.cells[cc++].innerHTML = qrow[c];', $script);
        self::assertStringContainsString("row.style.display = '';", $script);
        self::assertStringContainsString("row.style.display = 'none';", $script);
        self::assertStringEndsWith('    } // for', $script);
    }
}
