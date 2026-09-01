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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadProgressScriptBuilder;

final class QuickModeUploadProgressScriptBuilderTest extends TestCase
{
    public function testBuildsProgressUpdateCallback(): void
    {
        $script = QuickModeUploadProgressScriptBuilder::build("\n");

        self::assertStringContainsString("uploader.bind('UploadProgress', function(up, file) {", $script);
        self::assertStringContainsString("file.percent + '% <div style=\"height: 5px;width: '", $script);
        self::assertStringContainsString("getElementsByTagName('b')[0].innerHTML", $script);
        self::assertStringEndsWith("                                                                });", $script);
    }
}
