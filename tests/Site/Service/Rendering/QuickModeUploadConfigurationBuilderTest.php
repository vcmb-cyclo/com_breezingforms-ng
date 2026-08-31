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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadConfigurationBuilder;

final class QuickModeUploadConfigurationBuilderTest extends TestCase
{
    public function testBuildContainsStablePluploadConfiguration(): void
    {
        $script = (new QuickModeUploadConfigurationBuilder())->build(
            12,
            'upload',
            'ticket',
            '/site/',
            44,
            'html5,flash',
            'jpg,png',
            'true',
            '"Choose file"'
        );

        self::assertStringContainsString('max_retries: 10', $script);
        self::assertStringContainsString("multipart_params: { form: 12, itemName : 'upload'", $script);
        self::assertStringContainsString("url : '/site/index.php'", $script);
        self::assertStringContainsString('{title : "Choose file", extensions : \'jpg,png\'}', $script);
    }
}
