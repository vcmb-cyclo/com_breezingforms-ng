<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModePagingActionBuilder;

final class QuickModePagingActionBuilderTest extends TestCase
{
    public function testPreviousActionPreservesHistoricalValidationSequence(): void
    {
        self::assertSame(
            "ff_validate_prevpage(this, 'click');populateSummarizers();if(typeof bfRefreshAll != 'undefined'){bfRefreshAll();}",
            (new QuickModePagingActionBuilder())->previous()
        );
    }

    public function testNextActionPreservesHistoricalValidationSequence(): void
    {
        self::assertSame(
            "ff_validate_nextpage(this, 'click');populateSummarizers();if(typeof bfRefreshAll != 'undefined'){bfRefreshAll();}",
            (new QuickModePagingActionBuilder())->next()
        );
    }

    public function testOnePageNextActionSetsCurrentAndTargetPages(): void
    {
        self::assertSame(
            "ff_currentpage = 2;bf_validate_nextpage(3);populateSummarizers();if(typeof bfRefreshAll != 'undefined'){bfRefreshAll();}",
            (new QuickModePagingActionBuilder())->onePageNext(2, 3)
        );
    }
}
