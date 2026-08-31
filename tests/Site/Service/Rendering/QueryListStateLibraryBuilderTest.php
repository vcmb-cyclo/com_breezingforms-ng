<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListStateLibraryBuilder;

final class QueryListStateLibraryBuilderTest extends TestCase
{
    public function testBuildsStateEntriesInHistoricalOrder(): void
    {
        $entries = (new QueryListStateLibraryBuilder())->build('qcode();', "\n");

        self::assertSame(
            [
                'ff_queryCurrPage',
                'ff_queryPageSize',
                'ff_queryCols',
                'ff_queryCheckbox',
                'ff_queryHeader',
                'ff_queryPagenav',
                'ff_queryRows',
            ],
            array_column($entries, 0)
        );
        self::assertSame("var ff_queryRows = new Array();\nqcode();", $entries[6][1]);
    }
}
