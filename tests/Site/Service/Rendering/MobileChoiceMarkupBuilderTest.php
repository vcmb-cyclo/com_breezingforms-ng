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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\MobileChoiceMarkupBuilder;

final class MobileChoiceMarkupBuilderTest extends TestCase
{
    public function testBuildsMobileChoiceMarkup(): void
    {
        $html = (new MobileChoiceMarkupBuilder())->build('/form?mobile=1', 'Version mobile');

        self::assertStringContainsString('var bf_mobile_url = "\\/form?mobile=1";', $html);
        self::assertStringContainsString('<span>Version mobile</span>', $html);
        self::assertStringEndsWith('</div><div></div>', $html);
    }
}
