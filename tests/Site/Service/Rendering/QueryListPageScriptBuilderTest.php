<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListNavigationBuilder;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListPageScriptBuilder;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListPaginationTailBuilder;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListRowsRefreshBuilder;

final class QueryListPageScriptBuilderTest extends TestCase
{
    public function testAssemblesRefreshNavigationAndPaginationInHistoricalOrder(): void
    {
        $script = (new QueryListPageScriptBuilder(
            new QueryListRowsRefreshBuilder(),
            new QueryListNavigationBuilder(),
            new QueryListPaginationTailBuilder()
        ))->build(
            ['start' => 'Start', 'previous' => 'Previous', 'next' => 'Next', 'end' => 'End'],
            true,
            2,
            480,
            true,
            "\n"
        );

        self::assertStringStartsWith("function ff_dispQueryPage(id,page)\n{\n", $script);
        self::assertStringContainsString('var forced = false;', $script);
        self::assertStringContainsString('var qrows = ff_queryRows[id];', $script);
        self::assertStringContainsString("navi += ' Start';", $script);
        self::assertStringContainsString('ff_queryCurrPage[id] = page;', $script);
        self::assertStringContainsString('ff_selectAllQueryRows(id, false);', $script);
        self::assertStringContainsString('ff_resizepage(2, 480);', $script);
        self::assertStringEndsWith("    window.scrollTo(0,0);\n} // ff_dispQueryPage", $script);
    }

    public function testOmitsOptionalPaginationHooksWhenNotConfigured(): void
    {
        $script = (new QueryListPageScriptBuilder(
            new QueryListRowsRefreshBuilder(),
            new QueryListNavigationBuilder(),
            new QueryListPaginationTailBuilder()
        ))->build(
            ['start' => 'S', 'previous' => 'P', 'next' => 'N', 'end' => 'E'],
            false,
            0,
            0,
            false,
            "\n"
        );

        self::assertStringNotContainsString('ff_selectAllQueryRows', $script);
        self::assertStringNotContainsString('ff_resizepage', $script);
        self::assertStringNotContainsString('parent.window.scrollTo', $script);
        self::assertStringContainsString('window.scrollTo(0,0);', $script);
    }
}
