<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Runtime;

use HTML_facileFormsProcessor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\CodeToolsRuntime;

if (!defined('JPATH_ADMINISTRATOR')) {
    define('JPATH_ADMINISTRATOR', __DIR__ . '/../../../../administrator');
}

if (!defined('JPATH_SITE')) {
    define('JPATH_SITE', __DIR__ . '/../../../../');
}

require_once __DIR__ . '/../Rendering/QuickMode/joomla-cmsapplication-stub.php';
require_once __DIR__ . '/../Rendering/QuickMode/joomla-uri-stub.php';

if (!class_exists(HTML_facileFormsProcessor::class)) {
    require_once __DIR__ . '/../../../../components/com_breezingformsng/src/Support/processor_facade.php';
}

final class CodeToolsRuntimeTest extends TestCase
{
    public function testDelegatesStringAndJavascriptOperations(): void
    {
        $runtime = new CodeToolsRuntime($this->processor());
        $code = "  alert('ok');\n";

        self::assertTrue($runtime->trim($code));
        self::assertSame("alert('ok');", $code);
        self::assertSame(1, $runtime->nonblank($code));
        $whitespace = " \t\n";
        self::assertSame(0, $runtime->nonblank($whitespace));
        self::assertSame('true', $runtime->expJsValue(true));
        self::assertStringContainsString('answer = 42;', $runtime->expJsVar('answer', 42));
    }

    public function testDelegatesClassAndTraceModeResolution(): void
    {
        $runtime = new CodeToolsRuntime($this->processor(1, '_suffix'));

        self::assertSame('Bootstrap_suffix', $runtime->getClassName('Classic; Bootstrap'));
        self::assertSame('(direct minimum global none)', $runtime->dispTraceMode(_FF_TRACEMODE_DIRECT));
        self::assertSame('unchanged', $runtime->dispTraceMode('unchanged'));
    }

    private function processor(int $template = 0, string $suffix = ''): HTML_facileFormsProcessor
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $processor->template = $template;
        $processor->suffix = $suffix;

        return $processor;
    }
}
