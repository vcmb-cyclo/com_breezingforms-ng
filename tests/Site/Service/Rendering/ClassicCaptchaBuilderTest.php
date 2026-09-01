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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicCaptchaBuilder;

final class ClassicCaptchaBuilderTest extends TestCase
{
    public function testBuildsCaptchaMarkupWithDimensionsAndRefreshFlow(): void
    {
        $html = (new ClassicCaptchaBuilder())->build(100, 'position:absolute;', ' class="captcha"', '/captcha?form=7', '/site/', 230, 60);

        self::assertStringContainsString('id="ff_capimgValue" class="ff_capimg" src="/captcha?form=7"', $html);
        self::assertStringContainsString('style="width:230px;height:60px;"', $html);
        self::assertStringContainsString('name="bfCaptchaEntry" id="bfCaptchaEntry"', $html);
        self::assertStringContainsString('bfCaptchaEntry\').focus()', $html);
        self::assertStringContainsString('bfMathRandom=\' + Math.random()', $html);
        self::assertStringContainsString('/site/media/com_breezingformsng/images/site/captcha/refresh-captcha.png', $html);
    }

    public function testBuildsCaptchaWithoutOptionalDimensions(): void
    {
        $html = (new ClassicCaptchaBuilder())->build(101, '', '', '/captcha', '/', 0, 0, '  ', '', "\r\n");

        self::assertStringContainsString('style=""', $html);
        self::assertStringContainsString("  </div>\r\n", $html);
    }
}
