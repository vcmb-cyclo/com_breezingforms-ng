<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderReadonlyScriptWrapperBuilder;

final class ContentBuilderReadonlyScriptWrapperBuilderTest extends TestCase
{
    public function testWrapsReadonlyScriptWithHistoricalMarkers(): void
    {
        self::assertSame(
            "<script type=\"text/javascript\">\n<!--\nreadonly();\n//-->\n</script>\n",
            (new ContentBuilderReadonlyScriptWrapperBuilder())->build("readonly();\n")
        );
    }
}
