<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use HTML_facileFormsProcessor;
use Joomla\CMS\Application\CMSApplication;
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

require_once __DIR__ . '/QuickMode/joomla-text-stub.php';
require_once __DIR__ . '/QuickMode/joomla-uri-stub.php';
require_once __DIR__ . '/QuickMode/joomla-cmsapplication-stub.php';

if (!function_exists('Vcmb\\Component\\BreezingformsNG\\Site\\Service\\Rendering\\bf_b64dec')) {
    eval('namespace Vcmb\\Component\\BreezingformsNG\\Site\\Service\\Rendering; function bf_b64dec(string $value): string { return (string) base64_decode($value, true); }');
}

final class RenderingEngineProcessorDouble extends HTML_facileFormsProcessor
{
    public int $permissionChecks = 0;

    public function cbCheckPermissions(): array
    {
        $this->permissionChecks++;

        return [
            'form' => null,
            'record' => null,
            'frontend' => true,
            'data' => null,
            'full' => false,
        ];
    }
}

/**
 * Initial characterization coverage for RenderingEngine::view().
 *
 * The non-QuickMode guard is deliberately covered first: it is a complete
 * branch that must not initialize the QuickMode runtime or touch the database.
 */
final class RenderingEngineViewCharacterizationTest extends TestCase
{
    public function testNonQuickModeRendersWarningAndStopsBeforeRuntimeSetup(): void
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $processor->formrow = (object) ['template_code_processed' => 'LegacyTemplate'];

        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        (new ReflectionClass($engine))->getProperty('processor')->setValue($engine, $processor);

        ob_start();
        try {
            $engine->view();
            $html = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        self::assertSame(
            '<div class="alert alert-warning">COM_BREEZINGFORMSNG_QUICKMODE_ONLY</div>',
            $html
        );
    }

    public function testQuickModeStopsBeforeRenderingWhenProcessorCannotRun(): void
    {
        $processor = (new ReflectionClass(RenderingEngineProcessorDouble::class))->newInstanceWithoutConstructor();
        $processor->app = new class {
            public function getInput(): object
            {
                return new class {
                    public function getBool(string $name, bool $default = false): bool
                    {
                        return false;
                    }

                    public function getString(string $name, string $default = ''): string
                    {
                        return $name === 'ff_applic' ? 'mod_facileforms' : $default;
                    }

                    public function getInt(string $name, int $default = 0): int
                    {
                        return $default;
                    }
                };
            }

            public function getSession(): object
            {
                return new class {
                    public function clear(string $name): void
                    {
                    }

                    public function set(string $name, mixed $value): void
                    {
                    }
                };
            }
        };
        $processor->formrow = (object) [
            'template_code_processed' => 'QuickMode',
            'template_code' => base64_encode(json_encode([
                'properties' => [
                    'mobileEnabled' => false,
                    'forceMobile' => false,
                    'themebootstrapThemeEngine' => 'bootstrap',
                ],
            ], JSON_THROW_ON_ERROR)),
        ];
        $processor->okrun = false;

        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        (new ReflectionClass($engine))->getProperty('processor')->setValue($engine, $processor);

        ob_start();
        try {
            $engine->view();
            $html = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        self::assertSame('', $html);
        self::assertSame(1, $processor->permissionChecks);
    }

    public function testPermissionsReturnNeutralContextWhenContentBuilderIsUnavailable(): void
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        (new ReflectionClass($engine))->getProperty('processor')->setValue($engine, $processor);

        self::assertSame(
            [
                'form' => null,
                'record' => null,
                'frontend' => true,
                'data' => null,
                'full' => false,
            ],
            $engine->cbCheckPermissions()
        );
    }

    public function testHeaderRendersProcessorVariablesThroughSharedHeaderRenderer(): void
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $processor->app = new CMSApplication();
        $processor->okrun = true;
        $processor->ip = '127.0.0.1';
        $processor->agent = 'Test Agent';
        $processor->browser = 'Test Browser';
        $processor->opsys = 'Test OS';
        $processor->provider = 'Test Provider';
        $processor->submitted = 0;
        $processor->form = 12;
        $processor->form_id = 12;
        $processor->page = 1;
        $processor->target = '';
        $processor->runmode = 0;
        $processor->inframe = 0;
        $processor->inline = 0;
        $processor->template = 0;
        $processor->homepage = 'https://example.test';
        $processor->mossite = 'https://example.test';
        $processor->images = 0;
        $processor->border = 0;
        $processor->align = '';
        $processor->top = 0;
        $processor->suffix = '';
        $processor->status = '';
        $processor->message = '';
        $processor->record_id = 0;
        $processor->showgrid = false;
        $processor->traceBuffer = '';

        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        (new ReflectionClass($engine))->getProperty('processor')->setValue($engine, $processor);

        $GLOBALS['ff_config'] = (object) ['compress' => false];

        $script = $engine->header();

        self::assertStringContainsString('ff_processor = new Object();', $script);
        self::assertStringContainsString('ff_processor.form', $script);
        self::assertStringContainsString('= 12;', $script);
        self::assertStringContainsString("ff_processor.ip", $script);
        self::assertStringContainsString("'127.0.0.1';", $script);
    }

}
