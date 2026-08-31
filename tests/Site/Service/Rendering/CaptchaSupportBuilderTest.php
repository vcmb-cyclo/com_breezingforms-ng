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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\CaptchaSupportBuilder;

final class CaptchaSupportBuilderTest extends TestCase
{
    public function testEndpointsPreserveFrontendAndAdministratorPrefixes(): void
    {
        $builder = new CaptchaSupportBuilder();
        self::assertSame(
            'https://example.test/index.php?option=com_breezingformsng&bfCaptcha=1',
            $builder->endpoints('https://example.test', false, 7)['captcha']
        );
        self::assertStringContainsString(
            '/administrator/index.php',
            $builder->endpoints('https://example.test', true, 7)['image']
        );
        self::assertStringContainsString('form=7', $builder->endpoints('https://example.test', false, 7)['recaptcha']);
    }

    public function testValidationDefaultsKeepUnicodeAndCallback(): void
    {
        [$message, $callback] = (new CaptchaSupportBuilder())->validationDefaults('Erreur été');

        self::assertSame('"Erreur été"', $message);
        self::assertSame('function bfCheckCaptcha(){if(checkFileExtensions())ff_submitForm2();}', $callback);
    }
}
