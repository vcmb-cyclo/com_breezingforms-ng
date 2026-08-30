<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\PaymentMethodFieldBuilder;

final class PaymentMethodFieldBuilderTest extends TestCase
{
    public function testBuildReturnsThePaymentMethodHiddenField(): void
    {
        self::assertSame(
            "    <input type=\"hidden\" name=\"ff_payment_method\" id=\"bfPaymentMethod\" value=\"\"/>\n",
            (new PaymentMethodFieldBuilder())->build('    ')
        );
    }
}
