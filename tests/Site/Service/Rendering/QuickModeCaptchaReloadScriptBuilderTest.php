<?php

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
