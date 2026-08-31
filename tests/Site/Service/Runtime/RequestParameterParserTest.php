<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Runtime;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\RequestParameterParser;

final class RequestParameterParserTest extends TestCase
{
    public function testParsesAmpersandSeparatedParameters(): void
    {
        self::assertSame(
            ['foo' => 'bar', 'page' => '2', 'empty' => ''],
            (new RequestParameterParser())->parse(' foo = bar & page=2&empty')
        );
    }

    public function testParsesHtmlEncodedSeparators(): void
    {
        self::assertSame(
            ['option' => 'com_content', 'id' => '42'],
            (new RequestParameterParser())->parse('option=com_content&amp;id=42')
        );
    }

    public function testIgnoresBlankNamesAndPreservesEqualsInValues(): void
    {
        self::assertSame(
            ['token' => 'a=b=c'],
            (new RequestParameterParser())->parse('=ignored&& token = a=b=c')
        );
    }
}
