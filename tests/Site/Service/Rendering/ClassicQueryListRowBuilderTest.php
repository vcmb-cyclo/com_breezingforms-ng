<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicQueryListRowBuilder;

final class ClassicQueryListRowBuilderTest extends TestCase
{
    /** @return list<object> */
    private function columns(): array
    {
        return [
            (object) ['thspan' => 1, 'align' => 1, 'valign' => 0, 'wrap' => 0, 'class2' => 'odd', 'class3' => 'even', 'width' => 0, 'widthmd' => false],
            (object) ['thspan' => 1, 'align' => 3, 'valign' => 2, 'wrap' => 1, 'class2' => '', 'class3' => '', 'width' => 0, 'widthmd' => false],
        ];
    }

    public function testBuildsAlternatingRowAndDelegatesSelectionToCells(): void
    {
        $html = (new ClassicQueryListRowBuilder())->build($this->columns(), ['A', 'B'], 70, 3, 'items', ' class="odd-row"', 1, true, static fn (string $class): string => $class . '-resolved', static fn (): bool => false);

        self::assertStringContainsString('<tr class="odd-row">', $html);
        self::assertStringContainsString('class="odd-resolved"', $html);
        self::assertStringContainsString('type="checkbox"', $html);
        self::assertStringContainsString('value="A"', $html);
        self::assertStringContainsString('>B</td>', $html);
    }

    public function testStopsAfterProcessorDyingCallback(): void
    {
        $calls = 0;
        $html = (new ClassicQueryListRowBuilder())->build($this->columns(), ['A', 'B'], 71, 0, 'items', '', 0, false, static fn (string $class): string => $class, static function () use (&$calls): bool {
            return ++$calls === 1;
        });

        self::assertSame(1, substr_count($html, '<td'));
        self::assertStringContainsString('>A</td>', $html);
        self::assertStringNotContainsString('>B</td>', $html);
        self::assertStringEndsWith("</tr>\n", $html);
    }
}
