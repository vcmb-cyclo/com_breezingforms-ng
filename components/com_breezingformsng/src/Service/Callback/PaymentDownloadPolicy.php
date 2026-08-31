<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Callback;

/** Applies the shared limit for paid-file download attempts. */
final class PaymentDownloadPolicy
{
    public function canDownload(int $attempts, int $maximumAttempts): bool
    {
        return $attempts < $maximumAttempts;
    }
}
