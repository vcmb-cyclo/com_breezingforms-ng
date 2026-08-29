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

require_once __DIR__ . '/QuickMode/joomla-text-stub.php';

if (!function_exists('Vcmb\\Component\\BreezingformsNG\\Site\\Service\\Rendering\\bf_b64dec')) {
    eval('namespace Vcmb\\Component\\BreezingformsNG\\Site\\Service\\Rendering; function bf_b64dec(string $value): string { return (string) base64_decode($value, true); }');
}

final class RenderingEngineProcessorDouble extends HTML_facileFormsProcessor
{
    public function cbCheckPermissions(): array
    {
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

}
