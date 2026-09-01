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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FileExtensionsCheckBuilder;

final class FileExtensionsCheckBuilderTest extends TestCase
{
    public function testBuildsChecksForConfiguredFileUploads(): void
    {
        [$script, $count] = (new FileExtensionsCheckBuilder())->build(
            [
                (object) ['type' => 'File Upload', 'data2' => 'PDF, jpg', 'id' => 21, 'page' => 2],
                (object) ['type' => 'File Upload', 'data2' => '', 'id' => 22, 'page' => 1],
            ],
            2,
            '"Extension not allowed"',
            true
        );

        self::assertSame(1, $count);
        self::assertStringContainsString('ff_elem21Exts', $script);
        self::assertStringContainsString('lastIndexOf(".pdf")', $script);
        self::assertStringContainsString('lastIndexOf(".jpg")', $script);
        self::assertStringContainsString('return true;', $script);
        self::assertStringNotContainsString('ff_elem22Exts', $script);
    }

    public function testSkipsUploadsWhenTemplateIsNotConfigured(): void
    {
        [$script, $count] = (new FileExtensionsCheckBuilder())->build(
            [(object) ['type' => 'File Upload', 'data2' => 'pdf', 'id' => 21, 'page' => 1]],
            1,
            '"Extension not allowed"',
            false
        );

        self::assertSame(0, $count);
        self::assertStringNotContainsString('ff_elem21Exts', $script);
        self::assertStringContainsString('return true;', $script);
    }
}
