<?php

declare(strict_types=1);

namespace Joomla\CMS\Date {
    if (!class_exists(Date::class)) {
        class Date extends \DateTime
        {
            public function format(string $format, bool $local = false): string
            {
                return parent::format($format);
            }

            public function getOffsetFromGMT(): int
            {
                return (int) $this->format('Z');
            }
        }
    }
}

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Runtime {

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\SubmissionTimestampAdjuster;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\SubmissionTimestampFormatter;

final class SubmissionTimestampFormatterTest extends TestCase
{
    public function testFormatsSubmissionTimestampAndFileStamp(): void
    {
        $formatted = (new SubmissionTimestampFormatter())->format(
            '2024-01-02 03:04:05',
            'UTC'
        );

        self::assertSame('2024-01-02 03:04:05', $formatted->submittedAt);
        self::assertSame('20240102030405', $formatted->fileStamp);
    }

    public function testFormatsCustomPattern(): void
    {
        self::assertSame(
            '2024/01/02 03:04',
            (new SubmissionTimestampFormatter())->formatPattern(
                '2024-01-02 03:04:05',
                'UTC',
                'Y/m/d H:i'
            )
        );
    }

    public function testUsesTheSharedTimestampAdjuster(): void
    {
        $formatter = new SubmissionTimestampFormatter(new SubmissionTimestampAdjuster());

        self::assertSame(
            '2024-01-02 03:04:05',
            $formatter->format('2024-01-02 03:04:05', 'UTC')->submittedAt
        );
    }
}
}
