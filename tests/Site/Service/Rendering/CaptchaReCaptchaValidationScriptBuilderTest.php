<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\CaptchaReCaptchaValidationScriptBuilder;

final class CaptchaReCaptchaValidationScriptBuilderTest extends TestCase
{
    public function testBuildPreservesLegacyAndInvisibleReCaptchaFlows(): void
    {
        $script = (new CaptchaReCaptchaValidationScriptBuilder())->build(
            '"CAPTCHA"',
            '/index.php?option=com_breezingformsng',
            5
        );

        self::assertStringContainsString('var bfReCaptchaLoaded = true;', $script);
        self::assertStringContainsString('function bfValidateCaptcha()', $script);
        self::assertStringContainsString('url: "/index.php?option=com_breezingformsng"', $script);
        self::assertStringContainsString('if(ff_currentpage != 5)ff_switchpage(5);', $script);
        self::assertStringContainsString('grecaptcha.execute();', $script);
        self::assertStringContainsString('bfShowErrors("CAPTCHA");', $script);
    }
}
