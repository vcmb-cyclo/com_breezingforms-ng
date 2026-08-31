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

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeCaptchaReloadScriptBuilder;

final class QuickModeCaptchaReloadScriptBuilderTest extends TestCase
{
    public function testBuildsReloadScriptWithRandomQueryParameter(): void
    {
        self::assertSame(
            "document.getElementById('bfCaptchaEntry').value='';"
            . "document.getElementById('bfCaptchaEntry').focus();"
            . "document.getElementById('ff_capimgValue').src = '/captcha&bfMathRandom=' + Math.random(); return false",
            (new QuickModeCaptchaReloadScriptBuilder())->build('/captcha')
        );
    }
}
