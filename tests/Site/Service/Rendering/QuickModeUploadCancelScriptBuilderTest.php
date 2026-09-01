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

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadCancelScriptBuilder;

final class QuickModeUploadCancelScriptBuilderTest extends TestCase
{
    public function testBuildsCancellationAndBootstrapReactivation(): void
    {
        $script = QuickModeUploadCancelScriptBuilder::build('false', 62, true, "\n");

        self::assertStringContainsString("uploader_.removeFile(id_);", $script);
        self::assertStringContainsString("JQuery('#'+id_+'queueitem').remove();", $script);
        self::assertStringContainsString('bfFlashUploadersLength--;', $script);
        self::assertStringContainsString("JQuery('#bfPickFiles62').prop('disabled',false);", $script);
    }

    public function testBuildsClassicAndMobileDisplayReactivation(): void
    {
        $script = QuickModeUploadCancelScriptBuilder::build('false', 63, false, "\n");

        self::assertStringContainsString("JQuery('#bfPickFiles63').css('display','block');", $script);
        self::assertStringContainsString("JQuery('#bfPickFiles63holder').css('display','none');", $script);
        self::assertStringNotContainsString("JQuery('#bfPickFiles63').prop('disabled',false);", $script);
    }
}
