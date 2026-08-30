<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\AdditionalHiddenFieldsBuilder;

final class AdditionalHiddenFieldsBuilderTest extends TestCase
{
    public function testBuildsParametersInOrderWithUrlEncoding(): void
    {
        self::assertSame(
            "\t<input type=\"hidden\" name=\"alpha&amp;key\" value=\"a%2Bb\"/>\n"
            . "\t<input type=\"hidden\" name=\"second\" value=\"value+with+spaces\"/>\n",
            (new AdditionalHiddenFieldsBuilder())->build(
                ['alpha&key' => 'a+b', 'second' => 'value with spaces'],
                "\t",
                "\n"
            )
        );
    }

    public function testBuildsNoOutputForEmptyParameters(): void
    {
        self::assertSame('', (new AdditionalHiddenFieldsBuilder())->build([], ''));
    }
}
