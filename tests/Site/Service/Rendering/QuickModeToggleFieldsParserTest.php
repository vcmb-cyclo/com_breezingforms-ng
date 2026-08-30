<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeToggleFieldsParser;

final class QuickModeToggleFieldsParserTest extends TestCase
{
    public function testParsesRulesAndPreservesSpacesInValues(): void
    {
        $json = (new QuickModeToggleFieldsParser())->parse(
            "turn on element target if source is value with spaces\r\n"
            . "invalid rule\n"
            . "turn off section other if source is 0"
        );

        self::assertSame([
            [
                'action' => 'turn',
                'state' => 'on',
                'tCat' => 'element',
                'tName' => 'target',
                'statement' => 'if',
                'sName' => 'source',
                'condition' => 'is',
                'value' => 'value with spaces',
            ],
            [
                'action' => 'turn',
                'state' => 'off',
                'tCat' => 'section',
                'tName' => 'other',
                'statement' => 'if',
                'sName' => 'source',
                'condition' => 'is',
                'value' => '0',
            ],
        ], json_decode($json, true, 512, JSON_THROW_ON_ERROR));
    }

    public function testReturnsAnEmptyArrayForNoValidRules(): void
    {
        self::assertSame('[]', (new QuickModeToggleFieldsParser())->parse("\r\ninvalid"));
    }
}
