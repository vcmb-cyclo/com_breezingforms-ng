<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ClassicFileUploadBuilder;

final class ClassicFileUploadBuilderTest extends TestCase
{
    public function testBuildsFileInputWithOptionsAndEvents(): void
    {
        self::assertSame(
            "\t<div id=\"ff_div90\" style=\"position:absolute;\" class=\"wrapper\">\n\t\t<input id=\"ff_elem90\" size=\"2\" maxlength=\"100\" accept=\"image/png\" onclick=\"upload();\" type=\"file\" name=\"ff_nm_file[]\" class=\"control\"/>\n\t</div>\n",
            (new ClassicFileUploadBuilder())->build(90, 'file', 'position:absolute;', ' class="wrapper"', ' class="control"', 2, 100, false, 'image/png', ' onclick="upload();"')
        );
    }

    public function testBuildsDisabledFileInputWithoutOptionalAttributes(): void
    {
        $html = (new ClassicFileUploadBuilder())->build(91, 'attachment', '', '', '', 0, 0, true, '', '', '  ', "\r\n");

        self::assertStringContainsString('type="file"', $html);
        self::assertStringContainsString('disabled="disabled"', $html);
        self::assertStringNotContainsString('accept=', $html);
        self::assertStringContainsString('name="ff_nm_attachment[]"', $html);
    }
}
