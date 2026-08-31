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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderFileDisplayNameBuilder;

final class ContentBuilderFileDisplayNameBuilderTest extends TestCase
{
    public function testUsesBasenameEscapesMarkupAndRestoresWordWrapBreaks(): void
    {
        self::assertSame(
            'report<br>.pdf',
            (new ContentBuilderFileDisplayNameBuilder())->build('/tmp/report<br>.pdf')
        );
    }

    public function testKeepsSafeWordWrapBreakMarkup(): void
    {
        self::assertSame(
            'very-long<br>name.txt',
            (new ContentBuilderFileDisplayNameBuilder())->build('very-long<br>name.txt')
        );
    }

    public function testEscapesUnexpectedMarkup(): void
    {
        self::assertSame(
            '&lt;script&gt;.txt',
            (new ContentBuilderFileDisplayNameBuilder())->build('<script>.txt')
        );
    }
}
