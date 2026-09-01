<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator;

if (!defined('JPATH_SITE')) {
    define('JPATH_SITE', dirname(__DIR__, 2));
}

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Administrator\Helper\QuickmodeHtml;

final class QuickmodeHtmlTest extends TestCase
{
    public function testDecodeJsonArrayReturnsTheDecodedObject(): void
    {
        self::assertSame(
            ['properties' => ['themebootstrap' => 'Azure']],
            QuickmodeHtml::decodeJsonArray('{"properties":{"themebootstrap":"Azure"}}')
        );
    }

    public function testDecodeJsonArrayNormalizesInvalidJsonToAnEmptyArray(): void
    {
        self::assertSame([], QuickmodeHtml::decodeJsonArray('{invalid'));
    }
}
