<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormSubmissionFieldsBuilder;

final class FormSubmissionFieldsBuilderTest extends TestCase
{
    public function testBuildsFrontendSubmissionFields(): void
    {
        self::assertSame(
            "  <input type=\"hidden\" name=\"ff_form\" value=\"12\"/>\n"
            . "  <input type=\"hidden\" name=\"ff_task\" value=\"submit\"/>\n",
            (new FormSubmissionFieldsBuilder())->build(12, '  ', "\n")
        );
    }

    public function testBuildsBackendAndPreviewFields(): void
    {
        $builder = new FormSubmissionFieldsBuilder();

        self::assertStringContainsString('name="act" value="run"', $builder->build(13, "\t", "\r\n", true));
        self::assertStringContainsString('name="ff_frame" value="1"', $builder->build(14, "\t", "\r\n", false, true));
    }
}
