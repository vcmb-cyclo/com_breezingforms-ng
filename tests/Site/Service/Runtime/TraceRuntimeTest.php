<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Runtime;

use HTML_facileFormsProcessor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\TraceRuntime;

require_once __DIR__ . '/../Rendering/QuickMode/joomla-text-stub.php';
require_once __DIR__ . '/../Rendering/QuickMode/joomla-cmsapplication-stub.php';
require_once __DIR__ . '/../Rendering/QuickMode/joomla-uri-stub.php';

if (!class_exists(HTML_facileFormsProcessor::class)) {
    require_once __DIR__ . '/../../../../components/com_breezingformsng/src/Support/processor_facade.php';
}

final class TraceRuntimeTest extends TestCase
{
    public function testTraceAndTraceLineRecordMessagesAndUpdateTheCurrentLine(): void
    {
        $processor = $this->processor(_FF_TRACEMODE_MESSAGE);
        $processor->traceStack = [['mode', 'p', 'piece', 1, 'p', 2, 0]];
        $runtime = new TraceRuntime($processor);

        $runtime->trace('hello <world>');
        $runtime->traceLine(17, 'line <message>');

        self::assertStringContainsString('hello &lt;world&gt;', $processor->traceBuffer);
        self::assertStringContainsString('17: line &lt;message&gt;', $processor->traceBuffer);
        self::assertSame(17, $processor->traceStack[0][3]);
    }

    public function testPieceTracePushesAndPopsItsFrame(): void
    {
        $processor = $this->processor(_FF_TRACEMODE_FIRST);
        $runtime = new TraceRuntime($processor);
        $mode = _FF_TRACEMODE_PIECE;

        $runtime->tracePiece($mode, 'before', 4, 'p', 8, 0);
        self::assertCount(1, $processor->traceStack);
        self::assertStringContainsString('+COM_BREEZINGFORMSNG_PROCESS_ENTER before', $processor->traceBuffer);

        self::assertSame('result', $runtime->traceExit(9, 'result'));
        self::assertCount(0, $processor->traceStack);
        self::assertStringContainsString('-COM_BREEZINGFORMSNG_PROCESS_LEAVE before', $processor->traceBuffer);
    }

    public function testFunctionTraceFormatsSupportedArgumentKinds(): void
    {
        $processor = $this->processor(_FF_TRACEMODE_FIRST);
        $runtime = new TraceRuntime($processor);
        $mode = _FF_TRACEMODE_FUNCTION;
        $args = [null, true, 12, "line\nvalue", [], (object) []];

        $runtime->traceFunction($mode, 'submit', 22, 'f', 3, 0, $args);

        self::assertStringContainsString(
            "+COM_BREEZINGFORMSNG_PROCESS_ENTER submit(null, true, 12, &#039;line value&#039;, "
                . 'COM_BREEZINGFORMSNG_PROCESS_ARRAY, COM_BREEZINGFORMSNG_PROCESS_OBJECT)',
            $processor->traceBuffer
        );
        self::assertCount(1, $processor->traceStack);
    }

    public function testEmptyExitReportsAWarningAndReturnsItsValue(): void
    {
        $processor = $this->processor(_FF_TRACEMODE_FIRST);

        self::assertSame(42, (new TraceRuntime($processor))->traceExit(30, 42));
        self::assertStringContainsString('COM_BREEZINGFORMSNG_PROCESS_WARNSTK', $processor->traceBuffer);
    }

    public function testFacadeRetainsGlobalAdaptersWithoutTraceImplementation(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Support/processor_facade.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString('(new TraceRuntime($ff_processor))->traceFunction(', $source);
        self::assertStringContainsString('(new TraceRuntime($ff_processor))->traceExit(', $source);
        self::assertStringNotContainsString('COM_BREEZINGFORMSNG_PROCESS_ENTER', $source);
        self::assertStringNotContainsString('array_push($ff_processor->traceStack', $source);
    }

    private function processor(int $traceMode): HTML_facileFormsProcessor
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $processor->dying = false;
        $processor->traceMode = $traceMode;
        $processor->traceStack = [];
        $processor->traceBuffer = '';

        return $processor;
    }
}
