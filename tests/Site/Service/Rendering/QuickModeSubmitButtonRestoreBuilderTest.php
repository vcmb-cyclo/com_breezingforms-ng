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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeSubmitButtonRestoreBuilder;

final class QuickModeSubmitButtonRestoreBuilderTest extends TestCase
{
    public function testBuildsSubmitButtonRestorationCallback(): void
    {
        $script = (new QuickModeSubmitButtonRestoreBuilder())->build("\n");

        self::assertStringContainsString('function bf_restore_submitbutton(){', $script);
        self::assertStringContainsString('clone(true)', $script);
        self::assertStringContainsString('replaceWith( JQuery(cloned_submit) )', $script);
        self::assertStringContainsString('attr("id","bfSubmitButton")', $script);
        self::assertStringContainsString('Ladda.bind("#bfSubmitButton");', $script);
    }
}
