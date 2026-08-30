<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormOptionalContextFieldsBuilder;

final class FormOptionalContextFieldsBuilderTest extends TestCase
{
    public function testBuildsAllOptionalFrontendFieldsInOrder(): void
    {
        self::assertSame(
            "\t<input type=\"hidden\" name=\"ff_target\" value=\"2\"/>\n"
            . "\t<input type=\"hidden\" name=\"ff_frame\" value=\"1\"/>\n"
            . "\t<input type=\"hidden\" name=\"ff_border\" value=\"1\"/>\n"
            . "\t<input type=\"hidden\" name=\"ff_page\" value=\"3\"/>\n"
            . "\t<input type=\"hidden\" name=\"ff_align\" value=\"2\"/>\n"
            . "\t<input type=\"hidden\" name=\"ff_top\" value=\"4\"/>\n",
            (new FormOptionalContextFieldsBuilder())->build(2, true, true, 3, 2, 4, "\t", true, true, true, true, "\n")
        );
    }

    public function testPreviewOmitsLayoutFieldsAndDefaultsToEmpty(): void
    {
        $builder = new FormOptionalContextFieldsBuilder();

        self::assertSame(
            "<input type=\"hidden\" name=\"ff_page\" value=\"2\"/>\r\n",
            $builder->build(1, false, false, 2, 2, 4, '', false, false, false, false)
        );
        self::assertSame('', $builder->build(1, false, false, 1, 1, 0, '', false, false, false, false));
    }
}
