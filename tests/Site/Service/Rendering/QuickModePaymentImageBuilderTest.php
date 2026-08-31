<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModePaymentImageBuilder;

final class QuickModePaymentImageBuilderTest extends TestCase
{
    public function testBuildsOptionalPaymentImageAttributes(): void
    {
        self::assertSame(
            'src="/paypal.png" alt="PayPal" ',
            QuickModePaymentImageBuilder::build('/paypal.png', 'PayPal')
        );
        self::assertSame('', QuickModePaymentImageBuilder::build(''));
    }

    public function testEscapesPaymentImageAttributes(): void
    {
        $attributes = QuickModePaymentImageBuilder::build(
            '/pay" onerror="alert(1)',
            'Pay" onerror="alert(2)'
        );

        self::assertSame(
            'src="/pay&quot; onerror=&quot;alert(1)" alt="Pay&quot; onerror=&quot;alert(2)" ',
            $attributes
        );
    }
}
