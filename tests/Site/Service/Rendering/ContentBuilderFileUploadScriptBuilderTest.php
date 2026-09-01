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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderFileUploadScriptBuilder;

final class ContentBuilderFileUploadScriptBuilderTest extends TestCase
{
    public function testBuildCreatesDeletionControlsAndDeactivationScript(): void
    {
        $result = (new ContentBuilderFileUploadScriptBuilder())->build(
            27,
            'documents',
            2,
            ['first.pdf', 'second.pdf']
        );

        self::assertSame(2, $result['count']);
        self::assertSame(2, substr_count($result['html'], 'type=\"checkbox\"'));
        self::assertStringContainsString('cb_delete_27[0]', $result['html']);
        self::assertStringContainsString('cb_delete_27[1]', $result['html']);
        self::assertStringContainsString('first.pdf', $result['html']);
        self::assertSame("bfDeactivateField[\"ff_nm_documents[]\"]=true;\n", $result['deactivation']);
    }

    public function testBuildLeavesEmptyFileListWithoutControlsOrDeactivation(): void
    {
        $result = (new ContentBuilderFileUploadScriptBuilder())->build(27, 'documents', 1, []);

        self::assertSame(1, $result['count']);
        self::assertSame('', $result['html']);
        self::assertSame('', $result['deactivation']);
    }
}
