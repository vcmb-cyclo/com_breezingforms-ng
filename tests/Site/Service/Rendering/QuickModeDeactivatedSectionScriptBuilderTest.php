<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeDeactivatedSectionScriptBuilder;

final class QuickModeDeactivatedSectionScriptBuilderTest extends TestCase
{
    public function testBuildsHistoricalDeactivatedSectionScript(): void
    {
        self::assertSame(
            "<script type=\"text/javascript\">bfRegisterDeactivatedSection(\"billing\");</script>\n",
            (new QuickModeDeactivatedSectionScriptBuilder())->build('billing')
        );
    }

    public function testEscapesSectionNameAsJsonString(): void
    {
        $script = (new QuickModeDeactivatedSectionScriptBuilder())->build('billing"\nsection');

        self::assertStringContainsString('billing\\"', $script);
        self::assertStringContainsString('section', $script);
    }
}
