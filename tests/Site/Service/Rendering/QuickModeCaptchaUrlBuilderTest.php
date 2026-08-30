<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeCaptchaUrlBuilder;

final class QuickModeCaptchaUrlBuilderTest extends TestCase
{
    public function testBuildsFrontendCaptchaUrl(): void
    {
        self::assertSame(
            '/site/index.php?option=com_breezingformsng&bfCaptcha=1',
            (new QuickModeCaptchaUrlBuilder())->build('/site', false)
        );
    }

    public function testBuildsAdministratorCaptchaUrl(): void
    {
        self::assertSame(
            '/site/administrator/index.php?option=com_breezingformsng&bfCaptcha=1',
            (new QuickModeCaptchaUrlBuilder())->build('/site', true)
        );
    }
}
