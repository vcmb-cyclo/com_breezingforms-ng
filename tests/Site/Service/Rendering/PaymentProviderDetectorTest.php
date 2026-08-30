<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\PaymentProviderDetector;

final class PaymentProviderDetectorTest extends TestCase
{
    public function testDetectsEachSupportedProvider(): void
    {
        $detector = new PaymentProviderDetector();

        foreach (['PayPal', 'Sofortueberweisung', 'Stripe'] as $type) {
            self::assertTrue($detector->hasSupportedProvider([(object) ['type' => $type]]));
        }
    }

    public function testIgnoresUnsupportedAndEmptyRows(): void
    {
        self::assertFalse((new PaymentProviderDetector())->hasSupportedProvider([
            (object) ['type' => 'Text'],
            (object) [],
        ]));
        self::assertFalse((new PaymentProviderDetector())->hasSupportedProvider([]));
    }
}
