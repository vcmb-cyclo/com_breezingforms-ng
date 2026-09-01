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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderFlashUploadValidationBuilder;

final class ContentBuilderFlashUploadValidationBuilderTest extends TestCase
{
    public function testBuildsTheQuickModeValidationCallback(): void
    {
        $script = (new ContentBuilderFlashUploadValidationBuilder())->build();

        self::assertStringContainsString('function ff_flashupload_not_empty(element, message)', $script);
        self::assertStringContainsString('cbFlashElemCnt[element.id]', $script);
        self::assertStringContainsString('ff_validationFocus(element.name);', $script);
    }
}
