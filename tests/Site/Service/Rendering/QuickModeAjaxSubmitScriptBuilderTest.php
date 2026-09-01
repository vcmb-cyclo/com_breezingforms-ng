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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeAjaxSubmitScriptBuilder;

final class QuickModeAjaxSubmitScriptBuilderTest extends TestCase
{
    public function testBuildsThankYouPageAjaxFlow(): void
    {
        $script = QuickModeAjaxSubmitScriptBuilder::build(
            7,
            true,
            '"Error"',
            '"Success"',
            '"https://example.test/form"',
            "\n"
        );

        self::assertStringContainsString('function bf_ajax_submit(){', $script);
        self::assertStringContainsString('JQuery.post( url, JQuery("#7").serialize() )', $script);
        self::assertStringContainsString('if(true){', $script);
        self::assertStringContainsString('remodal();', $script);
        self::assertStringContainsString('alert("Success");', $script);
        self::assertStringContainsString('location.href = "https://example.test/form";', $script);
    }

    public function testBuildsDirectSuccessFlowWithoutThankYouPage(): void
    {
        $script = QuickModeAjaxSubmitScriptBuilder::build(9, false, '"Error"', '"Success"', '"/form"', "\n");

        self::assertStringContainsString('if(false){', $script);
        self::assertStringContainsString('JQuery(".bfPage").css("pointer-events","auto");', $script);
        self::assertStringContainsString('alert("Success");', $script);
        self::assertStringContainsString('location.href = "/form";', $script);
    }
}
