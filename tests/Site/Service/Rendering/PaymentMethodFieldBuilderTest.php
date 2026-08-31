<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

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
