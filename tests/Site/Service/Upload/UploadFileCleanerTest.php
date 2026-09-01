<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Upload;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\UploadFileCleaner;

final class UploadFileCleanerTest extends TestCase
{
    public function testRecognizesOnlyExpiredFourPartCacheNames(): void
    {
        $cleaner = new UploadFileCleaner();
        $now = 200000;

        self::assertTrue($cleaner->isExpiredPaymentCacheCandidate('payment_order_123_cache', $now - 86400, $now));
        self::assertFalse($cleaner->isExpiredPaymentCacheCandidate('payment_order_123', $now - 86400, $now));
        self::assertFalse($cleaner->isExpiredPaymentCacheCandidate('payment_order_123_cache_extra', $now - 86400, $now));
        self::assertFalse($cleaner->isExpiredPaymentCacheCandidate('payment_order_123_cache', 199999, $now));
    }

    public function testRecognizesExpiredTemporaryFilesWithTheirSuffix(): void
    {
        $cleaner = new UploadFileCleaner();

        self::assertTrue($cleaner->isExpiredTemporaryCandidate('1_2_3_4_flashtmp', 100, '_flashtmp', 86500));
        self::assertFalse($cleaner->isExpiredTemporaryCandidate('1_2_flashtmp', 100, '_flashtmp', 86500));
        self::assertFalse($cleaner->isExpiredTemporaryCandidate('1_2_3_4_chunktmp', 100, '_flashtmp', 86500));
        self::assertFalse($cleaner->isExpiredTemporaryCandidate('1_2_3_4_chunktmp', 100, '_chunktmp', 86499));
    }
}
