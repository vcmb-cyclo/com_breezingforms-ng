<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use HTML_facileFormsProcessor;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Uri\Uri;
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

    /** @var list<string> */
    public array $callbackNames = [];

    public bool $buryAfterFirstCallback = false;

    public function loadBuiltins(&$library)
    {
        $library['builtin'] = 'loaded';
    }

    public function loadScripts(&$library)
    {
        $library['script'] = 'loaded';
    }

    public function addFunction($cond, $id, $name, $code, &$library, &$linked, $type, $rowid, $pane)
    {
        $this->callbackNames[] = $name;
    }

    public function bury()
    {
        return $this->buryAfterFirstCallback && count($this->callbackNames) >= 1;
    }

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
        $processor = (new ReflectionClass(RenderingEngineProcessorDouble::class))->newInstanceWithoutConstructor();
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
        self::assertSame(0, $processor->permissionChecks);
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

    public function testFileExtensionValidationScriptCoversConfiguredUploads(): void
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $processor->formrow = (object) ['template_code' => 'encoded-template'];
        $processor->rowcount = 2;
        $processor->rows = [
            (object) ['type' => 'File Upload', 'data2' => 'PDF, jpg', 'id' => 21, 'page' => 2],
            (object) ['type' => 'File Upload', 'data2' => '', 'id' => 22, 'page' => 1],
        ];

        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        (new ReflectionClass($engine))->getProperty('processor')->setValue($engine, $processor);

        $method = (new ReflectionClass($engine))->getMethod('buildFileExtensionsCheck');
        [$script, $count] = $method->invoke($engine);

        self::assertSame(1, $count);
        self::assertStringContainsString('ff_elem21Exts', $script);
        self::assertStringContainsString('lastIndexOf(".pdf")', $script);
        self::assertStringContainsString('lastIndexOf(".jpg")', $script);
        self::assertStringContainsString('return true;', $script);
        self::assertStringNotContainsString('ff_elem22Exts', $script);

        $processor->formrow->template_code = '';
        [$emptyTemplateScript, $emptyTemplateCount] = $method->invoke($engine);

        self::assertSame(0, $emptyTemplateCount);
        self::assertStringContainsString('return true;', $emptyTemplateScript);
        self::assertStringNotContainsString('ff_elem21Exts', $emptyTemplateScript);
    }

    public function testScriptLibraryStateLoadsBuiltinsAndScripts(): void
    {
        $processor = (new ReflectionClass(RenderingEngineProcessorDouble::class))->newInstanceWithoutConstructor();
        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        (new ReflectionClass($engine))->getProperty('processor')->setValue($engine, $processor);

        $method = (new ReflectionClass($engine))->getMethod('createScriptLibraryState');
        [$library, $linked] = $method->invoke($engine);

        self::assertSame(['builtin' => 'loaded', 'script' => 'loaded'], $library);
        self::assertSame([], $linked);
    }

    public function testMobileChoiceRemovesDesktopOverrideAndAddsMobileFlag(): void
    {
        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        Uri::$currentUrl = 'https://example.test/form?foo=bar&non_mobile=1';

        ob_start();
        try {
            (new ReflectionClass($engine))->getMethod('renderMobileChoice')->invoke($engine);
            $html = ob_get_contents();
        } finally {
            ob_end_clean();
            Uri::$currentUrl = 'http://example.test/form';
        }

        self::assertStringContainsString(
            (string) json_encode('https://example.test/form?foo=bar&mobile=1'),
            $html
        );
        self::assertStringNotContainsString('non_mobile=1', $html);
        self::assertStringContainsString('COM_BREEZINGFORMSNG_MOBILE_VERSION', $html);
    }

    public function testCaptchaDefaultsUseFileExtensionCheckBeforeSubmit(): void
    {
        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        $method = (new ReflectionClass($engine))->getMethod('createCaptchaDefaults');
        [$error, $callback] = $method->invoke($engine);

        self::assertSame('"COM_BREEZINGFORMSNG_CAPTCHA_MISSING_WRONG"', $error);
        self::assertSame('function bfCheckCaptcha(){if(checkFileExtensions())ff_submitForm2();}', $callback);
    }

    public function testMobileChoiceTypeOnlyAppliesToOptionalMobileMode(): void
    {
        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        $method = (new ReflectionClass($engine))->getMethod('mobileChoiceType');

        self::assertSame('choose', $method->invoke($engine, true, [
            'mobileEnabled' => true,
            'forceMobile' => false,
        ]));
        self::assertSame('', $method->invoke($engine, false, [
            'mobileEnabled' => true,
            'forceMobile' => false,
        ]));
        self::assertSame('', $method->invoke($engine, true, [
            'mobileEnabled' => true,
            'forceMobile' => true,
        ]));
    }

    public function testApplyMobileModeActivatesForcedMobileAndDisablesDesktopWrapper(): void
    {
        require_once JPATH_ADMINISTRATOR . '/components/com_breezingformsng/libraries/crosstec/functions/helpers.php';

        $processor = (new ReflectionClass(RenderingEngineProcessorDouble::class))->newInstanceWithoutConstructor();
        $processor->app = new class {
            public function getInput(): object
            {
                return new class {
                    public function getString(string $name, string $default = ''): string
                    {
                        return $default;
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
                    public bool $mobilePreference = true;

                    public function get(string $name, mixed $default = null): mixed
                    {
                        return $name === 'com_breezingformsng.mobile' ? $this->mobilePreference : $default;
                    }
                };
            }
        };
        $processor->legacy_wrap = true;
        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        (new ReflectionClass($engine))->getProperty('processor')->setValue($engine, $processor);
        $method = (new ReflectionClass($engine))->getMethod('applyMobileMode');

        $previousUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Linux; Android 14; Mobile)';
        try {
            self::assertTrue($method->invoke($engine, [
                'mobileEnabled' => true,
                'forceMobile' => true,
            ]));
            self::assertTrue($processor->isMobile);
            self::assertTrue($processor->legacy_wrap);

            self::assertTrue($method->invoke($engine, [
                'mobileEnabled' => false,
                'forceMobile' => false,
            ]));
            self::assertFalse($processor->isMobile);

            self::assertTrue($method->invoke($engine, [
                'mobileEnabled' => true,
                'forceMobile' => false,
            ]));
            self::assertTrue($processor->isMobile);
        } finally {
            if ($previousUserAgent === null) {
                unset($_SERVER['HTTP_USER_AGENT']);
            } else {
                $_SERVER['HTTP_USER_AGENT'] = $previousUserAgent;
            }
        }

        self::assertFalse($method->invoke($engine, [
            'themebootstrapThemeEngine' => 'bootstrap',
        ]));
        self::assertFalse($processor->isMobile);
        self::assertFalse($processor->legacy_wrap);
    }

    public function testQuickModeMetadataLoadsPropertiesFromEncodedTemplate(): void
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $processor->formrow = (object) [
            'template_code' => base64_encode(json_encode([
                'properties' => [
                    'themebootstrapThemeEngine' => 'bootstrap',
                    'themebootstrapMode' => true,
                ],
                'elements' => [['type' => 'bfTextfield']],
            ], JSON_THROW_ON_ERROR)),
        ];
        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        (new ReflectionClass($engine))->getProperty('processor')->setValue($engine, $processor);

        $method = (new ReflectionClass($engine))->getMethod('loadQuickModeMetadata');

        self::assertSame([
            'themebootstrapThemeEngine' => 'bootstrap',
            'themebootstrapMode' => true,
        ], $method->invoke($engine));
    }

    public function testFormScriptsStopBetweenInitAndSubmittedWhenProcessorIsBuried(): void
    {
        $processor = (new ReflectionClass(RenderingEngineProcessorDouble::class))->newInstanceWithoutConstructor();
        $processor->form = 7;
        $processor->formrow = (object) [
            'name' => 'contact',
            'script1cond' => 0,
            'script1id' => 0,
            'script1code' => '',
            'script2cond' => 0,
            'script2id' => 0,
            'script2code' => '',
        ];
        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        (new ReflectionClass($engine))->getProperty('processor')->setValue($engine, $processor);
        $method = (new ReflectionClass($engine))->getMethod('addFormScripts');
        $library = [];
        $linked = [];

        self::assertFalse($method->invokeArgs($engine, [&$library, &$linked]));
        self::assertSame([
            'ff_contact_init',
            'ff_contact_submitted',
        ], $processor->callbackNames);

        $processor->callbackNames = [];
        $processor->buryAfterFirstCallback = true;
        self::assertTrue($method->invokeArgs($engine, [&$library, &$linked]));
        self::assertSame(['ff_contact_init'], $processor->callbackNames);
    }

    public function testMobileSessionPreferenceClearsDesktopOverrideBeforeSettingMobile(): void
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $input = new class {
            /** @var array<string, bool> */
            public array $values = [];

            public function getBool(string $name, bool $default = false): bool
            {
                return $this->values[$name] ?? $default;
            }
        };
        $session = new class {
            /** @var list<string> */
            public array $actions = [];

            public function clear(string $name): void
            {
                $this->actions[] = 'clear:' . $name;
            }

            public function set(string $name, mixed $value): void
            {
                $this->actions[] = 'set:' . $name . ':' . (int) $value;
            }
        };
        $processor->app = new class($input, $session) {
            public function __construct(private object $input, private object $session)
            {
            }

            public function getInput(): object
            {
                return $this->input;
            }

            public function getSession(): object
            {
                return $this->session;
            }
        };
        $engine = (new ReflectionClass(RenderingEngine::class))->newInstanceWithoutConstructor();
        (new ReflectionClass($engine))->getProperty('processor')->setValue($engine, $processor);
        $method = (new ReflectionClass($engine))->getMethod('syncMobileSessionPreference');

        $input->values = ['non_mobile' => true, 'mobile' => true];
        $method->invoke($engine);
        self::assertSame(['clear:com_breezingformsng.mobile'], $session->actions);

        $input->values = ['mobile' => true];
        $method->invoke($engine);
        self::assertSame([
            'clear:com_breezingformsng.mobile',
            'set:com_breezingformsng.mobile:1',
        ], $session->actions);

        $input->values = [];
        $method->invoke($engine);
        self::assertSame([
            'clear:com_breezingformsng.mobile',
            'set:com_breezingformsng.mobile:1',
        ], $session->actions);
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
