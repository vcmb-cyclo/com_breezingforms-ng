<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicQueryListCellBuilder;

final class ClassicQueryListCellBuilderTest extends TestCase
{
    public function testBuildsSelectableCellWithAttributesAndWidth(): void
    {
        $column = (object) [
            'thspan' => 2,
            'align' => 2,
            'valign' => 1,
            'wrap' => 1,
            'class2' => 'odd',
            'class3' => 'even',
            'width' => 30,
            'widthmd' => false,
        ];
        $skip = 0;
        $html = (new ClassicQueryListCellBuilder())->build($column, '42', 0, 3, 70, 'items', 1, true, $skip, static fn (string $class): string => 'resolved-' . $class);

        self::assertStringContainsString('valign="top"', $html);
        self::assertStringContainsString('nowrap="nowrap"', $html);
        self::assertStringContainsString('class="resolved-odd"', $html);
        self::assertStringContainsString('style="text-align:center;width:30px;"', $html);
        self::assertStringContainsString('type="checkbox"', $html);
        self::assertStringContainsString('ff_cb70_3', $html);
    }

    public function testBuildsRadioCellAndConsumesMergedColumnSkip(): void
    {
        $column = (object) ['thspan' => 3, 'align' => 0, 'valign' => 0, 'wrap' => 0, 'class2' => '', 'class3' => '', 'width' => 0, 'widthmd' => false];
        $skip = 0;
        $builder = new ClassicQueryListCellBuilder();

        self::assertStringContainsString('type="radio"', $builder->build($column, 'x', 0, 0, 71, 'items', 2, false, $skip, static fn (string $class): string => $class));
        self::assertSame(1, $skip);
        self::assertNotSame('', $builder->build($column, 'y', 1, 1, 71, 'items', 0, false, $skip, static fn (string $class): string => $class));
        self::assertSame(0, $skip);
    }
}
