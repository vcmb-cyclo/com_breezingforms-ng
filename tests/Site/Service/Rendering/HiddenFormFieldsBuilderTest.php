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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\HiddenFormFieldsBuilder;

final class HiddenFormFieldsBuilderTest extends TestCase
{
    public function testPaymentMethodReturnsThePaymentMethodHiddenField(): void
    {
        self::assertSame(
            "    <input type=\"hidden\" name=\"ff_payment_method\" id=\"bfPaymentMethod\" value=\"\"/>\n",
            (new HiddenFormFieldsBuilder())->paymentMethod('    ')
        );
    }

    public function testTokenFormatsTokenWithIndentationAndHistoricalNewline(): void
    {
        self::assertSame(
            "\t<input type=\"hidden\" name=\"token\" value=\"abc\"/>\r\n",
            (new HiddenFormFieldsBuilder())->token(
                '<input type="hidden" name="token" value="abc"/>',
                "\t"
            )
        );
    }

    public function testTokenPreservesEmptyTokenInput(): void
    {
        self::assertSame("\r\n", (new HiddenFormFieldsBuilder())->token('', ''));
    }

    public function testAdditionalBuildsParametersInOrderWithUrlEncoding(): void
    {
        self::assertSame(
            "\t<input type=\"hidden\" name=\"alpha&amp;key\" value=\"a%2Bb\"/>\n"
            . "\t<input type=\"hidden\" name=\"second\" value=\"value+with+spaces\"/>\n",
            (new HiddenFormFieldsBuilder())->additional(
                ['alpha&key' => 'a+b', 'second' => 'value with spaces'],
                "\t",
                "\n"
            )
        );
    }

    public function testAdditionalBuildsNoOutputForEmptyParameters(): void
    {
        self::assertSame('', (new HiddenFormFieldsBuilder())->additional([], ''));
    }

    public function testRoutingBuildsReturnAndComponentTemplateFields(): void
    {
        self::assertSame(
            '<input type="hidden" name="return" value="https&quot;next"/>' . "\r\n"
            . '<input type="hidden" name="tmpl" value="component"/>' . "\r\n",
            (new HiddenFormFieldsBuilder())->routing('https"next', 'component')
        );
    }

    public function testRoutingOmitsFieldsWhenRoutingParametersAreAbsent(): void
    {
        self::assertSame('', (new HiddenFormFieldsBuilder())->routing('', ''));
        self::assertSame(
            '<input type="hidden" name="return" value="next"/>' . "\r\n",
            (new HiddenFormFieldsBuilder())->routing('next', 'html')
        );
    }

    public function testSubmissionBuildsFrontendSubmissionFields(): void
    {
        self::assertSame(
            "  <input type=\"hidden\" name=\"ff_form\" value=\"12\"/>\n"
            . "  <input type=\"hidden\" name=\"ff_task\" value=\"submit\"/>\n",
            (new HiddenFormFieldsBuilder())->submission(12, '  ', "\n")
        );
    }

    public function testSubmissionBuildsBackendAndPreviewFields(): void
    {
        $builder = new HiddenFormFieldsBuilder();

        self::assertSame(
            "\t<input type=\"hidden\" name=\"option\" value=\"com_breezingformsng\"/>\r\n"
            . "\t<input type=\"hidden\" name=\"act\" value=\"run\"/>\r\n"
            . "\t<input type=\"hidden\" name=\"ff_form\" value=\"13\"/>\r\n"
            . "\t<input type=\"hidden\" name=\"ff_task\" value=\"submit\"/>\r\n",
            $builder->submission(13, "\t", "\r\n", true)
        );
        self::assertSame(
            "\t<input type=\"hidden\" name=\"option\" value=\"com_breezingformsng\"/>\r\n"
            . "\t<input type=\"hidden\" name=\"ff_frame\" value=\"1\"/>\r\n"
            . "\t<input type=\"hidden\" name=\"ff_form\" value=\"14\"/>\r\n"
            . "\t<input type=\"hidden\" name=\"ff_task\" value=\"submit\"/>\r\n",
            $builder->submission(14, "\t", "\r\n", false, true)
        );
    }

    public function testContextBuildsFieldsInProvidedOrderAndEscapesValues(): void
    {
        self::assertSame(
            "\t<input type=\"hidden\" name=\"ff_applic\" value=\"module&amp;name\"/>\r\n"
            . "\t<input type=\"hidden\" name=\"ff_record_id\" value=\"12\"/>\r\n",
            (new HiddenFormFieldsBuilder())->context(
                ['ff_applic' => 'module&name', 'ff_record_id' => 12],
                "\t"
            )
        );
    }

    public function testContextBuildsNoOutputForEmptyContext(): void
    {
        self::assertSame('', (new HiddenFormFieldsBuilder())->context([], ''));
    }
}
