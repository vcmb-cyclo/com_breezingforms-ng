<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QueryListSelectAllScriptBuilder;

final class QueryListSelectAllScriptBuilderTest extends TestCase
{
    public function testBuildsPagedSelectAllCallback(): void
    {
        $script = (new QueryListSelectAllScriptBuilder())->build("\n");

        self::assertStringContainsString('function ff_selectAllQueryRows(id,checked)', $script);
        self::assertStringContainsString('var currpage = ff_queryCurrPage[id];', $script);
        self::assertStringContainsString("document.getElementById('ff_cb'+id+'_'+curr).checked = checked;", $script);
        self::assertStringContainsString("document.getElementById('ff_cb'+id).checked = checked;", $script);
        self::assertStringEndsWith('} // ff_selectAllQueryRows', $script);
    }
}
