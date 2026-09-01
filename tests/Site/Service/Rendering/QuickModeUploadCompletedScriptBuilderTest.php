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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadCompletedScriptBuilder;

final class QuickModeUploadCompletedScriptBuilderTest extends TestCase
{
    public function testBuildsServerResponseAndQueueCleanupCallback(): void
    {
        $script = QuickModeUploadCompletedScriptBuilder::build("\n");

        self::assertStringContainsString("uploader.bind('FileUploaded', function(up, file, response) {", $script);
        self::assertStringContainsString("if(response.response!='')", $script);
        self::assertStringContainsString('alert(response.response);', $script);
        self::assertStringContainsString("JQuery('#'+file.id+'queue').remove();", $script);
        self::assertStringEndsWith('                                                                });', $script);
    }
}
