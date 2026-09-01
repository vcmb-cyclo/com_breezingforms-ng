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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\CaptchaValidationRowSelector;

final class CaptchaValidationRowSelectorTest extends TestCase
{
    public function testSelectsTheFirstCaptchaImmediately(): void
    {
        $captcha = (object) ['type' => 'Captcha', 'page' => 3];

        self::assertSame(
            $captcha,
            (new CaptchaValidationRowSelector())->select([
                (object) ['type' => 'ReCaptcha', 'page' => 1],
                $captcha,
                (object) ['type' => 'ReCaptcha', 'page' => 9],
            ], 3)
        );
    }

    public function testSelectsTheLastReCaptchaWhenThereIsNoCaptcha(): void
    {
        $selected = (new CaptchaValidationRowSelector())->select([
            (object) ['type' => 'ReCaptcha', 'page' => 1],
            (object) ['type' => 'Text', 'page' => 4],
            (object) ['type' => 'ReCaptcha', 'page' => 9],
        ], 3);

        self::assertNotNull($selected);
        self::assertSame('ReCaptcha', $selected->type);
        self::assertSame(9, $selected->page);
    }

    public function testHonorsRowCountAndReturnsNullWithoutCaptchaRows(): void
    {
        $selector = new CaptchaValidationRowSelector();

        self::assertNull($selector->select([
            (object) ['type' => 'Captcha', 'page' => 2],
        ], 0));
        self::assertNull($selector->select([
            (object) ['type' => 'Text', 'page' => 2],
        ], 1));
    }
}
