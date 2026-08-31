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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\CaptchaValidationDefaultsBuilder;

final class CaptchaValidationDefaultsBuilderTest extends TestCase
{
    public function testBuildPreservesJsonEncodingAndCallback(): void
    {
        self::assertSame(
            [
                '"Erreur CAPTCHA / été"',
                'function bfCheckCaptcha(){if(checkFileExtensions())ff_submitForm2();}',
            ],
            (new CaptchaValidationDefaultsBuilder())->build('Erreur CAPTCHA / été')
        );
    }
}
