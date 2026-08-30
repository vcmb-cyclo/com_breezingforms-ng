<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeCaptchaMarkupBuilder;

final class QuickModeCaptchaMarkupBuilderTest extends TestCase
{
    public function testBuildsCaptchaImageWithSuffix(): void
    {
        self::assertSame(
            '<img alt="" width="230" id="ff_capimgValue" class="ff_capimg" src="/captcha"/><br/><br/>' . "\n",
            (new QuickModeCaptchaMarkupBuilder())->buildImage('width="230" ', 'ff_capimgValue', 'ff_capimg', '/captcha', '<br/><br/>')
        );
    }

    public function testBuildsResponseInputWithLeadingStyle(): void
    {
        self::assertSame(
            '<input  style="width:200px;" autocomplete="off" class="ff_elem" type="text" name="bfCaptchaEntry" id="bfCaptchaEntry" />' . "\n",
            (new QuickModeCaptchaMarkupBuilder())->buildResponseInput(' style="width:200px;"', 'ff_elem')
        );
    }
}
