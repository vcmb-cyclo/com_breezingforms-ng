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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\CaptchaWrapperMarkupBuilder;

final class CaptchaWrapperMarkupBuilderTest extends TestCase
{
    public function testBuildsLegacyCaptchaWrapper(): void
    {
        self::assertSame(
            "<table style=\"display:none;width:100%;\" id=\"bfReCaptchaWrap\"><tr><td><div id=\"bfReCaptchaDiv\"></div></td></tr></table>\r\n",
            (new CaptchaWrapperMarkupBuilder())->build(true, "\r\n")
        );
    }

    public function testModernFormsDoNotReceiveLegacyWrapper(): void
    {
        self::assertSame('', (new CaptchaWrapperMarkupBuilder())->build(false));
    }
}
