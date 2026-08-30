<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the hidden payment-method field used by payment integrations.
 */
final class PaymentMethodFieldBuilder
{
    public function build(string $indentation): string
    {
        return $indentation . '<input type="hidden" name="ff_payment_method" id="bfPaymentMethod" value=""/>' . "\n";
    }
}
