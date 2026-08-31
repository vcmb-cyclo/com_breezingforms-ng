<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Upload;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\TemporaryUploadFileCleaner;

final class TemporaryUploadFileCleanerTest extends TestCase
{
    public function testRecognizesExpiredFlashTemporaryFiles(): void
    {
        $cleaner = new TemporaryUploadFileCleaner();

        self::assertTrue($cleaner->isExpiredCandidate('1_2_3_4_flashtmp', 100, '_flashtmp', 86500));
        self::assertFalse($cleaner->isExpiredCandidate('1_2_flashtmp', 100, '_flashtmp', 86500));
        self::assertFalse($cleaner->isExpiredCandidate('1_2_3_4_chunktmp', 100, '_flashtmp', 86500));
    }

    public function testDoesNotExpireARecentTemporaryFile(): void
    {
        self::assertFalse(
            (new TemporaryUploadFileCleaner())->isExpiredCandidate(
                '1_2_3_4_chunktmp',
                100,
                '_chunktmp',
                86499
            )
        );
    }
}
