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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\CaptchaLegacyValidationScriptBuilder;

final class CaptchaLegacyValidationScriptBuilderTest extends TestCase
{
    public function testBuildPreservesLegacyAjaxFlowAndInterpolatesContext(): void
    {
        $script = (new CaptchaLegacyValidationScriptBuilder())->build(
            '"CAPTCHA"',
            '/captcha.png',
            '/check-captcha?form=7&value=',
            3
        );

        self::assertStringContainsString('function bfAjaxObject101()', $script);
        self::assertStringContainsString('alert("CAPTCHA");', $script);
        self::assertStringContainsString(
            'document.getElementById(\'ff_capimgValue\').src = \'/captcha.png\' + Math.random();',
            $script
        );
        self::assertStringContainsString(
            'if(ff_currentpage != 3)ff_switchpage(3);',
            $script
        );
        self::assertStringContainsString(
            'ao.sndReq("get","/check-captcha?form=7&value="+document.getElementById("bfCaptchaEntry").value,"");',
            $script
        );
    }

    public function testEscapesEndpointsInsideJavaScriptStrings(): void
    {
        $script = (new CaptchaLegacyValidationScriptBuilder())->build(
            '"CAPTCHA"',
            "/captcha';alert(1);//</script>",
            '/check?value=";alert(2);//',
            3
        );

        self::assertStringNotContainsString("src = '/captcha';alert(1);//</script>'", $script);
        self::assertStringNotContainsString('sndReq("get","/check?value=";alert(2);//"+', $script);
        self::assertStringContainsString("\\u0027;alert(1);//\\u003C/script", $script);
        self::assertStringContainsString('value=\\u0022;alert(2);//', $script);
    }
}
