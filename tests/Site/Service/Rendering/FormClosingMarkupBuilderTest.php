<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormClosingMarkupBuilder;

final class FormClosingMarkupBuilderTest extends TestCase
{
    public function testBuildsModernWrapperClosure(): void
    {
        self::assertSame(
            "</div><!-- form end -->\n",
            (new FormClosingMarkupBuilder())->build(false, "\n")
        );
    }

    public function testBuildsLegacyWrapperClosure(): void
    {
        self::assertSame(
            "</div></div></div><div class=\"bfPage-bl\"><div class=\"bfPage-br\"><div class=\"bfPage-b\"></div></div></div></div><!-- form end -->\n",
            (new FormClosingMarkupBuilder())->build(true, "\n")
        );
    }
}
