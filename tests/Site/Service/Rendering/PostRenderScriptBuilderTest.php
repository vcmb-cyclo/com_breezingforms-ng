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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\PostRenderScriptBuilder;

final class PostRenderScriptBuilderTest extends TestCase
{
    public function testBuildDefersEditableFunctionUntilToggleFieldsAreLoaded(): void
    {
        $script = (new PostRenderScriptBuilder())->build('bfLoadEditable');

        self::assertStringContainsString('if(typeof bfLoadEditable != "undefined")', $script);
        self::assertStringContainsString('bfLoadEditable();', $script);
        self::assertStringContainsString('clearInterval(waitForToggleFields);', $script);
    }

    public function testBuildSupportsContentBuilderFunctions(): void
    {
        $builder = new PostRenderScriptBuilder();

        self::assertStringContainsString('bfLoadContentBuilderEditable();', $builder->build('bfLoadContentBuilderEditable'));
        self::assertStringContainsString('bfDisableContentBuilderFields();', $builder->build('bfDisableContentBuilderFields'));
    }
}
