<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use HTML_facileFormsProcessor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\OnePageRenderer;

if (!defined('JPATH_ADMINISTRATOR')) {
    define('JPATH_ADMINISTRATOR', __DIR__ . '/../../../../../administrator');
}

if (!class_exists(HTML_facileFormsProcessor::class)) {
    require_once __DIR__ . '/../../../../../components/com_breezingformsng/src/Support/processor_facade.php';
}

require_once __DIR__ . '/joomla-htmlhelper-stub.php';
require_once __DIR__ . '/joomla-text-stub.php';
require_once __DIR__ . '/joomla-uri-stub.php';

/**
 * Characterization coverage for the one-page QuickMode renderer.
 */
final class OnePageRendererCharacterizationTest extends TestCase
{
    public function testTextfieldElement(): void
    {
        $renderer = $this->makeRenderer();
        $node = [
            'attributes' => ['id' => 'element42'],
            'properties' => [
                'type' => 'element',
                'bfType' => 'bfTextfield',
                'dbId' => 42,
                'bfName' => 'firstname',
                'label' => 'Prénom',
                'hint' => '',
                'required' => true,
                'hideLabel' => false,
                'password' => false,
                'maxLength' => '',
                'size' => '',
                'placeholder' => 'Votre prénom',
                'value' => 'Jean',
                'tabIndex' => -1,
                'readonly' => false,
                'off' => false,
                'mailbackAsSender' => false,
                'icon' => '',
            ],
        ];

        ob_start();
        try {
            $renderer->process($node);
            $html = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        self::assertIsString($html);
        self::assertStringContainsString('name="ff_nm_firstname[]"', $html);
        self::assertStringContainsString('value="Jean"', $html);
        self::assertStringContainsString('id="ff_elem42"', $html);
        self::assertStringContainsString('Votre pr&eacute;nom', $html);
    }

    private function makeRenderer(): OnePageRenderer
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $processor->rowcount = 0;
        $processor->rows = [];

        $renderer = (new ReflectionClass(OnePageRenderer::class))->newInstanceWithoutConstructor();
        $this->setPrivate($renderer, 'p', $processor);
        $this->setPrivate($renderer, 'rootMdata', [
            'themebootstrapThemeEngine' => 'bootstrap',
            'themebootstrap' => '',
            'useErrorAlerts' => true,
        ]);
        $this->setPrivate($renderer, 'fadingClass', '');
        $this->setPrivate($renderer, 'language_tag', 'zz-ZZ');
        $this->setPrivate($renderer, 'bsClasses', [5 => [
            'controls' => '',
            'form-inline' => 'bf-form-inline',
            'form-group' => 'bf-form-group mb-3',
            'control-group' => 'mb-3',
            'control-label' => 'form-label',
            'form-control' => 'form-control',
            'icon-asterisk' => 'fas fa-asterisk',
            'icon-question-sign' => 'fas fa-question-circle',
        ]]);

        return $renderer;
    }

    private function setPrivate(object $object, string $property, mixed $value): void
    {
        (new ReflectionClass($object))->getProperty($property)->setValue($object, $value);
    }
}
