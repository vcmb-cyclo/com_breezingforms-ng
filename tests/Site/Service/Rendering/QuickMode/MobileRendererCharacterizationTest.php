<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use HTML_facileFormsProcessor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\MobileRenderer;

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
 * Characterization coverage for the mobile QuickMode renderer.
 */
final class MobileRendererCharacterizationTest extends TestCase
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
                'placeholder' => 'Votre prénom',
                'value' => 'Jean',
                'tabIndex' => -1,
                'readonly' => false,
                'off' => false,
                'mailbackAsSender' => false,
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

    public function testTextareaElement(): void
    {
        $html = $this->renderElement('bfTextarea', [
            'dbId' => 44,
            'bfName' => 'message',
            'label' => 'Message',
            'required' => true,
            'width' => '',
            'height' => '',
            'placeholder' => '',
            'value' => "Ligne 1\nLigne 2",
        ]);

        self::assertStringContainsString('name="ff_nm_message[]"', $html);
        self::assertStringContainsString("Ligne 1\nLigne 2", $html);
        self::assertStringContainsString('<textarea', $html);
    }

    public function testRadioGroupElement(): void
    {
        $html = $this->renderElement('bfRadioGroup', [
            'dbId' => 48,
            'bfName' => 'gender',
            'label' => 'Civilité',
            'required' => true,
            'group' => "0;Madame;mme\n1;Monsieur;mr",
        ]);

        self::assertStringContainsString('type="radio"', $html);
        self::assertStringContainsString('value="mme"', $html);
        self::assertStringContainsString('checked="checked"  class="ff_elem" ', $html);
        self::assertStringContainsString('Madame</label>', $html);
        self::assertStringContainsString('Monsieur</label>', $html);
    }

    public function testCheckboxGroupElement(): void
    {
        $html = $this->renderElement('bfCheckboxGroup', [
            'dbId' => 49,
            'bfName' => 'interests',
            'label' => "Centres d'intérêt",
            'group' => "0;Route;road\n1;VTT;mtb\n0;Piste;track",
        ]);

        self::assertStringContainsString('type="checkbox"', $html);
        self::assertStringContainsString('value="mtb"', $html);
        self::assertStringContainsString('Route</label>', $html);
    }

    public function testCheckboxElement(): void
    {
        $html = $this->renderElement('bfCheckbox', [
            'dbId' => 46,
            'bfName' => 'accept',
            'label' => "J'accepte les conditions",
            'required' => true,
            'checked' => false,
            'value' => '1',
            'mailbackAccept' => false,
        ]);

        self::assertStringContainsString('type="checkbox"', $html);
        self::assertStringContainsString('name="ff_nm_accept[]"', $html);
        self::assertStringNotContainsString('checked="checked"', $html);
    }

    public function testSelectElement(): void
    {
        $html = $this->renderElement('bfSelect', [
            'dbId' => 47,
            'bfName' => 'country',
            'label' => 'Pays',
            'multiple' => false,
            'width' => '',
            'height' => '',
            'list' => "1;France;fr\n0;Belgique;be\n0;Suisse;ch",
        ]);

        self::assertStringContainsString('<select', $html);
        self::assertStringContainsString('<option selected="selected" value="fr">France</option>', $html);
        self::assertStringContainsString('<option value="be">Belgique</option>', $html);
    }

    public function testSubmitButtonElement(): void
    {
        $html = $this->renderElement('bfSubmitButton', [
            'dbId' => 52,
            'hideLabel' => true,
            'src' => '',
            'value' => 'Envoyer',
            'actionClick' => 0,
            'actionFunctionName' => '',
        ]);

        self::assertStringContainsString('type="submit"', $html);
        self::assertStringContainsString('name="ff_nm_field[]"', $html);
        self::assertStringContainsString('Envoyer</span>', $html);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function renderElement(string $bfType, array $overrides): string
    {
        $renderer = $this->makeRenderer();

        $defaults = [
            'type' => 'element',
            'bfType' => $bfType,
            'dbId' => 1,
            'bfName' => 'field',
            'label' => 'Label',
            'hint' => '',
            'required' => false,
            'hideLabel' => false,
            'password' => false,
            'maxLength' => '',
            'placeholder' => '',
            'value' => '',
            'tabIndex' => -1,
            'readonly' => false,
            'off' => false,
            'mailbackAsSender' => false,
        ];

        $node = [
            'attributes' => ['id' => 'element' . ($overrides['dbId'] ?? 1)],
            'properties' => array_merge($defaults, $overrides),
        ];

        ob_start();
        try {
            $renderer->process($node);
            $html = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        self::assertIsString($html);

        return $html;
    }

    private function makeRenderer(): MobileRenderer
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $processor->rowcount = 0;
        $processor->rows = [];

        $renderer = (new ReflectionClass(MobileRenderer::class))->newInstanceWithoutConstructor();
        $this->setPrivate($renderer, 'p', $processor);
        $this->setPrivate($renderer, 'rootMdata', ['useErrorAlerts' => true]);
        $this->setPrivate($renderer, 'language_tag', 'zz-ZZ');

        return $renderer;
    }

    private function setPrivate(object $object, string $property, mixed $value): void
    {
        (new ReflectionClass($object))->getProperty($property)->setValue($object, $value);
    }
}
