<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderFileValueParser;

final class ContentBuilderFileValueParserTest extends TestCase
{
    public function testNormalizesLineEndingsWithoutChangingFileCount(): void
    {
        self::assertSame(
            ['count' => 3, 'files' => ['first.pdf', 'second.pdf', '']],
            (new ContentBuilderFileValueParser())->parse("first.pdf\r\nsecond.pdf\r\n")
        );
    }

    public function testRepresentsAnEmptyStoredValueAsOneEmptyEntry(): void
    {
        self::assertSame(
            ['count' => 1, 'files' => ['']],
            (new ContentBuilderFileValueParser())->parse('')
        );
    }

    public function testPreservesBlankLinesAndUnicodeFileNames(): void
    {
        self::assertSame(
            [
                'count' => 4,
                'files' => ['rapport été.pdf', '', 'second.txt', ''],
            ],
            (new ContentBuilderFileValueParser())->parse("rapport été.pdf\r\n\nsecond.txt\n")
        );
    }

    public function testRemovesCarriageReturnsEvenWithoutLineFeeds(): void
    {
        self::assertSame(
            ['count' => 1, 'files' => ['first.pdfsecond.pdf']],
            (new ContentBuilderFileValueParser())->parse("first.pdf\rsecond.pdf")
        );
    }
}
