<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeReCaptchaInitScriptBuilder;

final class QuickModeReCaptchaInitScriptBuilderTest extends TestCase
{
    public function testBuildsTheVisibleGoogleApiUrlForTheActiveLanguage(): void
    {
        self::assertSame(
            'https://www.google.com/recaptcha/api.js?hl=fr&onload=onloadBFNewRecaptchaCallback&render=explicit',
            (new QuickModeReCaptchaInitScriptBuilder())->visibleApiUrl('fr-FR')
        );
    }

    public function testBuildsTheInvisibleGoogleApiUrl(): void
    {
        self::assertSame(
            'https://www.google.com/recaptcha/api.js?onload=onloadBFNewRecaptchaCallback&render=explicit',
            (new QuickModeReCaptchaInitScriptBuilder())->invisibleApiUrl()
        );
    }

    public function testBuildsVisibleRecaptchaInitializationScript(): void
    {
        self::assertSame(
            '<script data-usercentrics="reCAPTCHA" type="text/javascript">'
            . 'bfInitVisibleReCaptcha({"sitekey":"public-key","theme":"light","size":"normal",'
            . '"resetOnRerender":false});</script>',
            (new QuickModeReCaptchaInitScriptBuilder())->visible([
                'sitekey' => 'public-key',
                'theme' => 'light',
                'size' => 'normal',
                'resetOnRerender' => false,
            ])
        );
    }

    public function testJsonEncodingProtectsConfigurationValues(): void
    {
        $script = (new QuickModeReCaptchaInitScriptBuilder())->visible([
            'sitekey' => 'key";alert(1);//',
            'theme' => 'dark',
        ]);

        self::assertStringContainsString('key\\";alert(1);\\/\\/', $script);
        self::assertStringNotContainsString('key";alert(1);//', $script);
    }

    public function testEncodesInvisibleRecaptchaConfiguration(): void
    {
        self::assertSame(
            '{"sitekey":"public-key","badge":"bottomright","hasFlashUpload":false,"resetFlagOnCallback":true}',
            (new QuickModeReCaptchaInitScriptBuilder())->encode([
                'sitekey' => 'public-key',
                'badge' => 'bottomright',
                'hasFlashUpload' => false,
                'resetFlagOnCallback' => true,
            ])
        );
    }
}
