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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListPaginationTailBuilder;

final class QueryListPaginationTailBuilderTest extends TestCase
{
    public function testBuildsAllOptionalStatements(): void
    {
        $code = (new QueryListPaginationTailBuilder())->build(true, 2, 480, true, "\n");

        self::assertSame(
            "    if (checkbox) ff_selectAllQueryRows(id, false);\n"
            . "    ff_resizepage(2, 480);\n"
            . "    parent.window.scrollTo(0,0);\n"
            . "    window.scrollTo(0,0);\n",
            $code
        );
    }

    public function testAlwaysScrollsAndOmitsDisabledOptions(): void
    {
        self::assertSame(
            "    window.scrollTo(0,0);\n",
            (new QueryListPaginationTailBuilder())->build(false, 0, 0, false, "\n")
        );
    }
}
