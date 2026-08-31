<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModePaymentButtonBuilder;

final class QuickModePaymentButtonBuilderTest extends TestCase
{
    public function testBuildsSubmitPaymentButtonAndCallback(): void
    {
        self::assertSame(
            '<input class="ff_elem" value="PayPal" tabindex="1" '
                . 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Stripe\';pay(this,\'click\');" '
                . "type=\"submit\" name=\"ff_nm_payment[]\" id=\"ff_elem55\"/>\n",
            QuickModePaymentButtonBuilder::build(
                'Stripe',
                'payment',
                55,
                '',
                'PayPal',
                '',
                'tabindex="1" ',
                '',
                '',
                '',
                '',
                '',
                true,
                'pay'
            )
        );
    }

    public function testBuildsImageButtonAndEscapesFieldName(): void
    {
        $markup = QuickModePaymentButtonBuilder::build(
            'PayPal',
            'payment"><script>alert(1)</script>',
            55,
            '/paypal.png',
            'PayPal',
            'PayPal',
            '',
            '',
            '',
            '',
            '',
            '',
            false,
            ''
        );

        self::assertStringContainsString('type="image"', $markup);
        self::assertStringContainsString('src="/paypal.png" alt="PayPal"', $markup);
        self::assertStringContainsString(
            'name="ff_nm_payment&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;[]"',
            $markup
        );
        self::assertStringNotContainsString('<script>', $markup);
    }
}
