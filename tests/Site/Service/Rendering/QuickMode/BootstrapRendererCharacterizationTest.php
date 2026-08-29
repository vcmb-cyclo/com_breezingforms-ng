<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use HTML_facileFormsProcessor;
use Joomla\CMS\Application\CMSApplication;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\BootstrapRenderer;

if (!defined('JPATH_ADMINISTRATOR')) {
    define('JPATH_ADMINISTRATOR', __DIR__ . '/../../../../../administrator');
}

if (!class_exists(HTML_facileFormsProcessor::class)) {
    require_once __DIR__ . '/../../../../../components/com_breezingformsng/src/Support/processor_facade.php';
}

require_once __DIR__ . '/joomla-htmlhelper-stub.php';
require_once __DIR__ . '/joomla-text-stub.php';
require_once __DIR__ . '/joomla-uri-stub.php';
require_once __DIR__ . '/joomla-cmsapplication-stub.php';

/**
 * Characterization tests for BootstrapRenderer::process() - proves the same
 * ReflectionClass::newInstanceWithoutConstructor() harness used for
 * ClassicRenderer (see ClassicRendererCharacterizationTest) generalizes to
 * the other QuickMode renderers, not just the one it was first built for.
 *
 * BootstrapRenderer additionally reads $this->bsClasses[5][$key] via
 * bsClass() for its Bootstrap-5 CSS class names. That map is built inline in
 * the (bypassed) constructor, so a trimmed copy - only the keys the covered
 * field types actually use - is injected here directly. Keep this in sync
 * manually; a real drift shows up as a snapshot failure, not silently.
 */
final class BootstrapRendererCharacterizationTest extends TestCase
{
    private const SNAPSHOT_DIR = __DIR__ . '/__snapshots__';

    /** @var array<string, string> */
    private const BS_CLASSES = [
        'controls' => '',
        'form-inline' => 'bf-form-inline',
        'control-group' => 'mb-3',
        'control-label' => 'form-label',
        'form-control' => 'form-control',
        'form-group' => 'bf-form-group mb-3',
        'icon-question-sign' => 'fas fa-question-circle',
        'icon-asterisk' => 'fas fa-asterisk',
        'nonform-control' => 'nonform-control',
        'radio-form-group' => 'radio-form-group',
        'checkbox-form-group' => 'checkbox-form-group',
        'inline' => 'form-check-inline',
        'radio' => 'form-check-label',
        'checkbox' => 'form-check-label',
        'form-select' => 'form-select',
        'other-form-group' => 'other-form-group',
        'btn' => 'btn',
        'btn-primary' => 'btn-primary',
        'icon-upload' => 'fas fa-upload',
        'row' => 'row',
        'img-thumbnail' => 'img-thumbnail',
        'input-append' => 'input-group',
        'custom-form-control' => 'custom-form-control',
        'icon-refresh' => 'fas fa-sync',
    ];

    public function testTextfieldElement(): void
    {
        $renderer = $this->makeRenderer();

        $html = $this->render($renderer, $this->elementNode('bfTextfield', [
            'dbId' => 42,
            'bfName' => 'firstname',
            'label' => 'Prénom',
            'hint' => '',
            'required' => true,
            'password' => false,
            'maxLength' => '',
            'size' => '',
            'placeholder' => 'Votre prénom',
            'value' => '',
            'icon' => '',
        ]));

        $this->assertMatchesSnapshot('bootstrap_bfTextfield.html', $html);
    }

    public function testTextfieldElementWithExistingValue(): void
    {
        $renderer = $this->makeRenderer();

        $html = $this->render($renderer, $this->elementNode('bfTextfield', [
            'dbId' => 43,
            'bfName' => 'lastname',
            'label' => 'Nom',
            'hint' => '',
            'required' => false,
            'password' => false,
            'maxLength' => 40,
            'size' => '20em',
            'placeholder' => '',
            'value' => 'Valeur "pré-remplie" & spéciale',
            'icon' => 'fa-user',
        ]));

        $this->assertMatchesSnapshot('bootstrap_bfTextfield_prefilled.html', $html);
    }

    public function testTextareaElement(): void
    {
        $renderer = $this->makeRenderer();

        $html = $this->render($renderer, $this->elementNode('bfTextarea', [
            'dbId' => 44,
            'bfName' => 'message',
            'label' => 'Message',
            'required' => true,
            'width' => '',
            'height' => '',
            'value' => "Ligne 1\nLigne 2",
            'is_html' => false,
        ]));

        $this->assertMatchesSnapshot('bootstrap_bfTextarea.html', $html);
    }

    public function testRadioGroupElement(): void
    {
        $renderer = $this->makeRenderer();

        $html = $this->render($renderer, $this->elementNode('bfRadioGroup', [
            'dbId' => 48,
            'bfName' => 'gender',
            'label' => 'Civilité',
            'required' => true,
            'wrap' => true,
            'group' => "0;Madame;mme\n1;Monsieur;mr",
        ]));

        $this->assertMatchesSnapshot('bootstrap_bfRadioGroup.html', $html);
    }

    public function testCheckboxGroupElement(): void
    {
        $renderer = $this->makeRenderer();

        $html = $this->render($renderer, $this->elementNode('bfCheckboxGroup', [
            'dbId' => 49,
            'bfName' => 'interests',
            'label' => "Centres d'intérêt",
            'wrap' => false,
            'group' => "0;Route;road\n1;VTT;mtb\n0;Piste;track",
        ]));

        $this->assertMatchesSnapshot('bootstrap_bfCheckboxGroup.html', $html);
    }

    public function testCheckboxElement(): void
    {
        $renderer = $this->makeRenderer();

        $html = $this->render($renderer, $this->elementNode('bfCheckbox', [
            'dbId' => 46,
            'bfName' => 'accept',
            'label' => "J'accepte les conditions",
            'required' => true,
            'checked' => false,
            'value' => '1',
            'mailbackAccept' => false,
        ]));

        $this->assertMatchesSnapshot('bootstrap_bfCheckbox.html', $html);
    }

    public function testSelectElement(): void
    {
        $renderer = $this->makeRenderer();

        $html = $this->render($renderer, $this->elementNode('bfSelect', [
            'dbId' => 47,
            'bfName' => 'country',
            'label' => 'Pays',
            'multiple' => false,
            'width' => '',
            'height' => '',
            'list' => "1;France;fr\n0;Belgique;be\n0;Suisse;ch",
        ]));

        $this->assertMatchesSnapshot('bootstrap_bfSelect.html', $html);
    }

    public function testSubmitButtonElement(): void
    {
        $renderer = $this->makeRenderer();

        $html = $this->render($renderer, $this->elementNode('bfSubmitButton', [
            'dbId' => 52,
            'hideLabel' => true,
            'src' => '',
            'value' => 'Envoyer',
            'actionClick' => 0,
            'actionFunctionName' => '',
        ]));

        $this->assertMatchesSnapshot('bootstrap_bfSubmitButton.html', $html);
    }

    public function testFileElementPlain(): void
    {
        $renderer = $this->makeRenderer();

        $html = $this->render($renderer, $this->elementNode('bfFile', [
            'dbId' => 61,
            'bfName' => 'attachment',
            'hideLabel' => true,
            'flashUploader' => false,
            'html5' => false,
            'attachToAdminMail' => false,
            'attachToUserMail' => false,
        ]));

        $this->assertMatchesSnapshot('bootstrap_bfFile_plain.html', $html);
    }

    public function testFileElementFlashUploader(): void
    {
        $renderer = $this->makeRenderer();

        $html = $this->render($renderer, $this->elementNode('bfFile', [
            'dbId' => 62,
            'bfName' => 'photo',
            'hideLabel' => true,
            'flashUploader' => true,
            'html5' => true,
            'flashUploaderMulti' => false,
            'flashUploaderBytes' => 2097152,
            'allowedFileExtensions' => 'jpg,png',
            'attachToAdminMail' => true,
            'attachToUserMail' => false,
        ]));

        $this->assertMatchesSnapshot('bootstrap_bfFile_flashUploader.html', $html);
    }

    public function testCaptchaElement(): void
    {
        $renderer = $this->makeRenderer();

        $html = $this->render($renderer, $this->elementNode('bfCaptcha', [
            'dbId' => 56,
            'hideLabel' => true,
            'width' => '',
        ]));

        $this->assertMatchesSnapshot('bootstrap_bfCaptcha.html', $html);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function elementNode(string $bfType, array $overrides): array
    {
        $defaults = [
            'type' => 'element',
            'bfType' => $bfType,
            'dbId' => 1,
            'bfName' => 'field',
            'label' => 'Label',
            'hint' => '',
            'required' => false,
            'hideLabel' => false,
            'labelPosition' => 'left',
            'readonly' => false,
            'tabIndex' => -1,
            'off' => false,
            'theme' => '',
            'password' => false,
            'maxLength' => '',
            'size' => '',
            'placeholder' => '',
            'value' => '',
            'icon' => '',
            'mailbackAsSender' => false,
        ];

        $properties = array_merge($defaults, $overrides);

        return [
            'attributes' => ['id' => 'element' . $properties['dbId']],
            'properties' => $properties,
        ];
    }

    private function makeRenderer(): BootstrapRenderer
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $processor->rowcount = 0;
        $processor->rows = [];
        $processor->app = new CMSApplication();
        $processor->form = 27;

        $renderer = (new ReflectionClass(BootstrapRenderer::class))->newInstanceWithoutConstructor();

        $this->setPrivate($renderer, 'p', $processor);
        $this->setPrivate($renderer, 'rootMdata', [
            'joomlaHint' => false,
            'useErrorAlerts' => true,
            'themebootstrapThemeEngine' => 'bootstrap',
            'themebootstrap' => '',
        ]);
        $this->setPrivate($renderer, 'fadingClass', '');
        $this->setPrivate($renderer, 'language_tag', 'zz-ZZ');
        $this->setPrivate($renderer, 'bsClasses', [5 => self::BS_CLASSES]);

        return $renderer;
    }

    /**
     * @param array<string, mixed> $node
     */
    private function render(BootstrapRenderer $renderer, array $node): string
    {
        ob_start();
        try {
            $renderer->process($node);
        } finally {
            $html = ob_get_clean();
        }

        return $html;
    }

    private function setPrivate(object $object, string $property, mixed $value): void
    {
        $ref = new ReflectionClass($object);
        $ref->getProperty($property)->setValue($object, $value);
    }

    private function assertMatchesSnapshot(string $file, string $actual): void
    {
        $path = self::SNAPSHOT_DIR . '/' . $file;
        $updating = getenv('BF_UPDATE_SNAPSHOTS') === '1';

        if (!is_file($path)) {
            if (!$updating) {
                self::markTestIncomplete(
                    "No snapshot yet at tests/Site/Service/Rendering/QuickMode/__snapshots__/{$file} - "
                    . 'run with BF_UPDATE_SNAPSHOTS=1 to create it, review it, then commit it.'
                );
            }

            file_put_contents($path, $actual);
        } elseif ($updating) {
            file_put_contents($path, $actual);
        }

        self::assertSame(file_get_contents($path), $actual);
    }
}
