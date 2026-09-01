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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListSelectAllScriptBuilder;

final class QueryListSelectAllScriptBuilderTest extends TestCase
{
    public function testBuildsPagedSelectAllCallback(): void
    {
        $script = (new QueryListSelectAllScriptBuilder())->build("\n");

        self::assertStringContainsString('function ff_selectAllQueryRows(id,checked)', $script);
        self::assertStringContainsString('var currpage = ff_queryCurrPage[id];', $script);
        self::assertStringContainsString("document.getElementById('ff_cb'+id+'_'+curr).checked = checked;", $script);
        self::assertStringContainsString("document.getElementById('ff_cb'+id).checked = checked;", $script);
        self::assertStringEndsWith('} // ff_selectAllQueryRows', $script);
    }
}
