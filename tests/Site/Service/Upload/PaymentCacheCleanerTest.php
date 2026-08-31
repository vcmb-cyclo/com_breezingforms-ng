<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Upload;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\PaymentCacheCleaner;

final class PaymentCacheCleanerTest extends TestCase
{
    public function testRecognizesOnlyExpiredFourPartCacheNames(): void
    {
        $cleaner = new PaymentCacheCleaner();
        $now = 200000;

        self::assertTrue($cleaner->isExpiredCandidate('payment_order_123_cache', $now - 86400, $now));
        self::assertFalse($cleaner->isExpiredCandidate('payment_order_123', $now - 86400, $now));
        self::assertFalse($cleaner->isExpiredCandidate('payment_order_123_cache_extra', $now - 86400, $now));
    }

    public function testRejectsRecentCacheFiles(): void
    {
        $cleaner = new PaymentCacheCleaner();

        self::assertFalse($cleaner->isExpiredCandidate('payment_order_123_cache', 199999, 200000));
    }
}
