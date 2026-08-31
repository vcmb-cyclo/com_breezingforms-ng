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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeRemodalCloseScriptBuilder;

final class QuickModeRemodalCloseScriptBuilderTest extends TestCase
{
    public function testBuildsCartOrFormRedirectCallback(): void
    {
        $script = QuickModeRemodalCloseScriptBuilder::build('"https://example.test/form"', "\n");

        self::assertStringContainsString('function bf_remodal_close(){', $script);
        self::assertStringContainsString('location.href = crbc_cart_url;', $script);
        self::assertStringContainsString('location.href = "https://example.test/form";', $script);
        self::assertStringEndsWith("}\n", $script);
    }
}
