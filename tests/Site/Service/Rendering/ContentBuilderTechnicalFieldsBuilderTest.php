<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderTechnicalFieldsBuilder;

final class ContentBuilderTechnicalFieldsBuilderTest extends TestCase
{
    public function testBuildIncludesFormRecordAndNewMarkers(): void
    {
        $fields = (new ContentBuilderTechnicalFieldsBuilder())->build('    ', 12, 34, true);

        self::assertSame(
            "    <input type=\"hidden\" name=\"cb_form_id\" value=\"12\"/>\n"
            . "    <input type=\"hidden\" name=\"cb_record_id\" value=\"34\"/>\n"
            . "    <input type=\"hidden\" name=\"cbIsNew\" value=\"1\"/>\n",
            $fields
        );
    }

    public function testBuildOmitsOptionalFieldsAndReturnsEmptyWithoutForm(): void
    {
        $builder = new ContentBuilderTechnicalFieldsBuilder();

        self::assertSame(
            "<input type=\"hidden\" name=\"cb_form_id\" value=\"12\"/>\n",
            $builder->build('', 12, 0, false)
        );
        self::assertSame('', $builder->build('    ', 0, 34, true));
    }
}
