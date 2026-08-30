<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\CaptchaValidationDefaultsBuilder;

final class CaptchaValidationDefaultsBuilderTest extends TestCase
{
    public function testBuildPreservesJsonEncodingAndCallback(): void
    {
        self::assertSame(
            [
                '"Erreur CAPTCHA / été"',
                'function bfCheckCaptcha(){if(checkFileExtensions())ff_submitForm2();}',
            ],
            (new CaptchaValidationDefaultsBuilder())->build('Erreur CAPTCHA / été')
        );
    }
}
