<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\CaptchaEndpointBuilder;

final class CaptchaEndpointBuilderTest extends TestCase
{
    public function testBuildsSiteEndpoints(): void
    {
        self::assertSame(
            [
                'image' => 'https://example.test/index.php?option=com_breezingformsng&bfCaptcha=1&bfMathRandom=',
                'check' => 'https://example.test/index.php?raw=true&option=com_breezingformsng&checkCaptcha=true&Itemid=0&tmpl=component&value=',
                'recaptcha' => 'index.php?raw=true&option=com_breezingformsng&bfReCaptcha=true&form=27&Itemid=0&tmpl=component',
            ],
            (new CaptchaEndpointBuilder())->build('https://example.test', false, 27)
        );
    }

    public function testAddsAdministratorPrefixToSiteEndpoints(): void
    {
        $endpoints = (new CaptchaEndpointBuilder())->build('https://example.test', true, 31);

        self::assertSame(
            'https://example.test/administrator/index.php?option=com_breezingformsng&bfCaptcha=1&bfMathRandom=',
            $endpoints['image']
        );
        self::assertStringStartsWith(
            'https://example.test/administrator/index.php?raw=true&option=com_breezingformsng&checkCaptcha=true',
            $endpoints['check']
        );
        self::assertStringContainsString('&form=31&', $endpoints['recaptcha']);
    }
}
