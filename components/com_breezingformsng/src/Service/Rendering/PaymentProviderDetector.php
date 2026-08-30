<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Detects whether a form contains a supported payment provider field.
 */
final class PaymentProviderDetector
{
    /**
     * @param iterable<object> $rows
     */
    public function hasSupportedProvider(iterable $rows): bool
    {
        foreach ($rows as $row) {
            if (in_array($row->type ?? null, ['PayPal', 'Sofortueberweisung', 'Stripe'], true)) {
                return true;
            }
        }

        return false;
    }
}
