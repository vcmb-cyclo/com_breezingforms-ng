<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormTokenFieldBuilder;

final class FormTokenFieldBuilderTest extends TestCase
{
    public function testFormatsTokenWithIndentationAndHistoricalNewline(): void
    {
        self::assertSame(
            "\t<input type=\"hidden\" name=\"token\" value=\"abc\"/>\r\n",
            (new FormTokenFieldBuilder())->build(
                '<input type="hidden" name="token" value="abc"/>',
                "\t"
            )
        );
    }

    public function testPreservesEmptyTokenInput(): void
    {
        self::assertSame("\r\n", (new FormTokenFieldBuilder())->build('', ''));
    }
}
