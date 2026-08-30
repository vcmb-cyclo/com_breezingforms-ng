<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use HTML_facileFormsProcessor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\RenderingEngine;

if (!defined('JPATH_ADMINISTRATOR')) {
    define('JPATH_ADMINISTRATOR', __DIR__ . '/../../../../administrator');
}

if (!defined('JPATH_SITE')) {
    define('JPATH_SITE', __DIR__ . '/../../../../');
}

if (!class_exists(HTML_facileFormsProcessor::class)) {
    require_once __DIR__ . '/../../../../components/com_breezingformsng/src/Support/processor_facade.php';
}

if (!function_exists('Vcmb\\Component\\BreezingformsNG\\Site\\Service\\Rendering\\nl')) {
    eval('namespace Vcmb\\Component\\BreezingformsNG\\Site\\Service\\Rendering; function nl(): string { return "\\n"; }');
}

if (!function_exists('impstring')) {
    eval('function impstring(string $value): string { return $value; }');
}

final class QueryListProcessorDouble extends HTML_facileFormsProcessor
{
    /** @var list<array<int, scalar>> */
    public array $resultRows = [];

    public function __construct()
    {
    }

    public function trim(&$code)
    {
        return trim((string) $code);
    }

    public function compileQueryCol(&$elem, &$coldef)
    {
    }

    public function execQuery(&$elem, &$valrows, &$coldefs)
    {
        $valrows = $this->resultRows;
    }

    public function expJsValue($mixed, $indent = '')
    {
        return json_encode($mixed, JSON_THROW_ON_ERROR);
    }
}

final class RenderingEngineQueryListPreparationTest extends TestCase
{
    public function testQueryListPreparationExportsColumnsPaginationAndRows(): void
    {
        $processor = new QueryListProcessorDouble();
        $processor->queryCols = [];
        $processor->queryRows = [];
        $processor->resultRows = [['alpha', 2]];

        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        (new ReflectionClass($engine))->getProperty('processor')->setValue($engine, $processor);

        $row = (object) [
            'id' => 41,
            'flag1' => 1,
            'flag2' => 2,
            'height' => 25,
            'data1' => implode("\n", ['', '', '', '', '', '', '', '', '4']),
            'data3' => "Visible&first&&&&&&1\nHidden&second&&&&&&0",
        ];
        $checkboxCount = 0;
        $queryCode = '';

        (new ReflectionClass($engine))->getMethod('prepareQueryListRow')->invokeArgs(
            $engine,
            [$row, &$checkboxCount, &$queryCode]
        );

        self::assertSame(1, $checkboxCount);
        self::assertCount(2, $processor->queryCols['ff_41']);
        self::assertSame([['alpha', 2]], $processor->queryRows['ff_41']);
        self::assertSame(
            "\n" .
            "ff_queryCurrPage[41] = 1;\n" .
            "ff_queryPageSize[41] = 25;\n" .
            "ff_queryCheckbox[41] = 2;\n" .
            "ff_queryHeader[41] = 1;\n" .
            "ff_queryPagenav[41] = 4;\n" .
            "ff_queryCols[41] = [1,0];\n" .
            "ff_queryRows[41] = [[\"alpha\",2]];\n",
            $queryCode
        );
    }
}
