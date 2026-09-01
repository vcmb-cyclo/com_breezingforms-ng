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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModePagingActionBuilder;

final class QuickModePagingActionBuilderTest extends TestCase
{
    public function testPreviousActionPreservesHistoricalValidationSequence(): void
    {
        self::assertSame(
            "ff_validate_prevpage(this, 'click');populateSummarizers();if(typeof bfRefreshAll != 'undefined'){bfRefreshAll();}",
            (new QuickModePagingActionBuilder())->previous()
        );
    }

    public function testNextActionPreservesHistoricalValidationSequence(): void
    {
        self::assertSame(
            "ff_validate_nextpage(this, 'click');populateSummarizers();if(typeof bfRefreshAll != 'undefined'){bfRefreshAll();}",
            (new QuickModePagingActionBuilder())->next()
        );
    }

    public function testOnePageNextActionSetsCurrentAndTargetPages(): void
    {
        self::assertSame(
            "ff_currentpage = 2;bf_validate_nextpage(3);populateSummarizers();if(typeof bfRefreshAll != 'undefined'){bfRefreshAll();}",
            (new QuickModePagingActionBuilder())->onePageNext(2, 3)
        );
    }

    public function testCancelActionPreservesHistoricalResetCall(): void
    {
        self::assertSame(
            "ff_resetForm(this, 'click');",
            (new QuickModePagingActionBuilder())->cancel()
        );
    }
}
