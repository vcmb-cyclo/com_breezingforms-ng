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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicQueryListHeaderBuilder;

final class ClassicQueryListHeaderBuilderTest extends TestCase
{
    public function testBuildsHeaderWithSelectionColumnAndSpans(): void
    {
        $columns = [
            (object) ['thspan' => 1, 'thalign' => 2, 'thvalign' => 1, 'thwrap' => 1, 'class1' => 'head', 'width' => 0, 'widthmd' => false, 'title' => 'Name'],
            (object) ['thspan' => 2, 'thalign' => 3, 'thvalign' => 0, 'thwrap' => 0, 'class1' => '', 'width' => 20, 'widthmd' => false, 'title' => 'Details'],
        ];
        $html = (new ClassicQueryListHeaderBuilder())->build(
            $columns,
            70,
            1,
            ' class="header"',
            static fn (string $class): string => 'resolved-' . $class,
            static fn (object $column): string => '<b>' . $column->title . '</b>'
        );

        self::assertStringContainsString('ff_selectAllQueryRows(70,this.checked)', $html);
        self::assertStringContainsString('class="resolved-head"', $html);
        self::assertStringContainsString('nowrap="nowrap"', $html);
        self::assertStringContainsString('<b>Details</b>', $html);
        self::assertStringContainsString('colspan="2"', $html);
    }

    public function testBuildsRadioSelectionHeaderAndSkipsMergedColumn(): void
    {
        $columns = [
            (object) ['thspan' => 2, 'thalign' => 0, 'thvalign' => 0, 'thwrap' => 0, 'class1' => '', 'width' => 0, 'widthmd' => false, 'title' => 'Merged'],
            (object) ['thspan' => 1, 'thalign' => 0, 'thvalign' => 0, 'thwrap' => 0, 'class1' => '', 'width' => 0, 'widthmd' => false, 'title' => 'Second'],
        ];
        $html = (new ClassicQueryListHeaderBuilder())->build($columns, 71, 2, '', static fn (string $class): string => $class, static fn (object $column): string => $column->title);

        self::assertStringContainsString('<th colspan="2"></th>', $html);
        self::assertSame(1, substr_count($html, '<th'));
        self::assertStringNotContainsString('Second', $html);
    }
}
