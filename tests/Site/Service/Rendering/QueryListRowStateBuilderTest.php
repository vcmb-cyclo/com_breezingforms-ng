<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListRowStateBuilder;

final class QueryListRowStateBuilderTest extends TestCase
{
    public function testBuildsHistoricalQueryListStateAssignments(): void
    {
        self::assertSame(
            "\nff_queryCurrPage[32] = 1;\nff_queryPageSize[32] = 15;\n"
            . "ff_queryCheckbox[32] = 1;\nff_queryHeader[32] = 1;\n"
            . "ff_queryPagenav[32] = 3;\nff_queryCols[32] = [1,0,1];\n"
            . "ff_queryRows[32] = [[\"result\",1]];\n",
            (new QueryListRowStateBuilder())->build(
                32,
                15,
                1,
                1,
                3,
                [1, 0, 1],
                '[["result",1]]',
                "\n"
            )
        );
    }
}
