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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadThumbnailScriptBuilder;

final class QuickModeUploadThumbnailScriptBuilderTest extends TestCase
{
    public function testBuildsMoxieThumbnailAndFallbackReaderFlow(): void
    {
        $script = QuickModeUploadThumbnailScriptBuilder::build('/site/', "\n");

        self::assertStringContainsString('function bfUploadImageThumb(file) {', $script);
        self::assertStringContainsString("['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']", $script);
        self::assertStringContainsString('new FileReader()', $script);
        self::assertStringContainsString('reader.readAsDataURL(nativeFile);', $script);
        self::assertStringContainsString(
            "moxie.core.utils.Url.resolveUrl('/site/components/com_breezingformsng/libraries/jquery/plupload/"
            . "Moxie.swf')",
            $script
        );
        self::assertStringContainsString('img.onerror = function()', $script);
    }

    public function testUsesConfiguredLineSeparator(): void
    {
        $script = QuickModeUploadThumbnailScriptBuilder::build('/base/', '|');

        self::assertStringContainsString('function bfUploadImageThumb(file) {|', $script);
        self::assertStringContainsString('bfFallbackThumb();|', $script);
    }

    public function testEscapesBaseUrlInsideJavaScriptString(): void
    {
        $script = QuickModeUploadThumbnailScriptBuilder::build("/base/';alert(1);//</script>", "\n");

        self::assertStringNotContainsString("resolveUrl('/base/';alert(1);//</script>", $script);
        self::assertStringContainsString('resolveUrl(\'/base/\\u0027;alert(1);//\\u003C/script\\u003E', $script);
    }
}
