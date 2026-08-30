<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormOpeningMarkupBuilder;

final class FormOpeningMarkupBuilderTest extends TestCase
{
    public function testBuildsModernWrapperWithOptionalClass(): void
    {
        self::assertSame(
            '<div id="ff_formdiv12" class="bfFormDiv custom-form">',
            (new FormOpeningMarkupBuilder())->build('12', 'custom-form', false)
        );
    }

    public function testBuildsLegacyWrapperAndNewline(): void
    {
        self::assertSame(
            "<div id=\"ff_formdiv12\" class=\"bfFormDiv\"><div class=\"bfPage-tl\"><div class=\"bfPage-tr\"><div class=\"bfPage-t\"></div></div></div><div class=\"bfPage-l\"><div class=\"bfPage-r\"><div class=\"bfPage-m bfClearfix\">\n",
            (new FormOpeningMarkupBuilder())->build('12', '', true, "\n")
        );
    }
}
