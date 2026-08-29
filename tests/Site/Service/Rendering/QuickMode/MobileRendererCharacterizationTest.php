<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use HTML_facileFormsProcessor;
use Joomla\CMS\Application\CMSApplication;
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
require_once __DIR__ . '/joomla-cmsapplication-stub.php';

/**
 * Characterization coverage for the mobile QuickMode renderer.
 */
final class MobileRendererCharacterizationTest extends TestCase
{
    private const SNAPSHOT_DIR = __DIR__ . '/__snapshots__';

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
        $this->assertMatchesSnapshot('mobile_bfTextfield.html', $html);
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

    public function testFileElementPlain(): void
    {
        $html = $this->renderElement('bfFile', [
            'dbId' => 61,
            'bfName' => 'attachment',
            'hideLabel' => true,
            'flashUploader' => false,
            'html5' => false,
            'attachToAdminMail' => false,
            'attachToUserMail' => false,
        ], 'plain');

        self::assertStringContainsString('type="file"', $html);
        self::assertStringContainsString('name="ff_nm_attachment[]"', $html);
    }

    public function testFileElementFlashUploader(): void
    {
        $html = $this->renderElement('bfFile', [
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
        ], 'flashUploader');

        self::assertStringContainsString('flashUploadphoto', $html);
        self::assertStringContainsString('plupload.Uploader', $html);
        self::assertStringContainsString('name="attachToAdminMail[photo]"', $html);
    }

    public function testCaptchaElement(): void
    {
        $html = $this->renderElement('bfCaptcha', [
            'dbId' => 56,
            'hideLabel' => true,
        ]);

        self::assertStringContainsString('id="ff_capimgValue"', $html);
        self::assertStringContainsString('bfCaptcha=1', $html);
        self::assertStringContainsString('name="bfCaptchaEntry"', $html);
    }

    public function testReCaptchaElement(): void
    {
        $html = $this->renderElement('bfReCaptcha', [
            'dbId' => 59,
            'hideLabel' => true,
            'pubkey' => '6Lc-test-pubkey',
            'invisibleCaptcha' => false,
            'theme' => '',
            'size' => '',
        ]);

        self::assertStringContainsString('newrecaptcha', $html);
        self::assertStringContainsString('bfInitVisibleReCaptcha', $html);
        self::assertStringContainsString('"sitekey":"6Lc-test-pubkey"', $html);
    }

    /**
     * bfCalendar itself is deliberately not covered: unlike the other
     * renderers (which delegate to HTMLHelper::_('calendar', ...)),
     * MobileRenderer implements its own calendar() widget builder that
     * calls LayoutHelper::render('joomla.form.field.calendar', ...) and
     * $this->p->database->getNullDate() - a real Joomla core layout file
     * plus a live DB connection, neither reasonably fakeable in this
     * pure-logic harness without adding far more risk of the double
     * silently diverging from the real thing than it's worth for one
     * field type. bfCalendarResponsive (below) doesn't touch either and
     * is fully covered.
     */
    public function testCalendarResponsiveElement(): void
    {
        $html = $this->renderElement('bfCalendarResponsive', [
            'dbId' => 58,
            'bfName' => 'eventdate',
            'label' => 'Date',
            'required' => true,
            'value' => '',
            'format' => '%Y-%m-%d',
            'firstDay' => '1',
        ]);

        self::assertStringContainsString('bfInitCalendarResponsive', $html);
        self::assertStringContainsString('name="ff_nm_eventdate[]"', $html);
    }

    public function testHiddenElement(): void
    {
        $html = $this->renderElement('bfHidden', [
            'dbId' => 45,
            'bfName' => 'source',
            'hideLabel' => true,
            'value' => 'newsletter-2026',
        ]);

        self::assertStringContainsString('type="hidden"', $html);
        self::assertStringContainsString('value="newsletter-2026"', $html);
    }

    public function testNumberInputElement(): void
    {
        $html = $this->renderElement('bfNumberInput', [
            'dbId' => 50,
            'bfName' => 'age',
            'label' => 'Âge',
            'range' => false,
            'value' => '',
            'maxLength' => '',
        ]);

        self::assertStringContainsString('type="number"', $html);
        self::assertStringContainsString('name="ff_nm_age[]"', $html);
    }

    public function testSummarizeElement(): void
    {
        $html = $this->renderElement('bfSummarize', [
            'dbId' => 51,
            'connectWith' => ['price', 'qty'],
            'connectType' => 'multiply',
            'emptyMessage' => '0',
            'hideIfEmpty' => false,
            'fieldCalc' => '',
        ]);

        self::assertStringContainsString('bfRegisterSummarize', $html);
        self::assertStringContainsString('"multiply"', $html);
    }

    public function testSignatureElement(): void
    {
        $html = $this->renderElement('bfSignature', [
            'dbId' => 57,
            'bfName' => 'signature',
            'hideLabel' => true,
        ]);

        self::assertStringContainsString('bfSignature57', $html);
        self::assertStringContainsString('name="ff_nm_signature[]"', $html);
    }

    public function testStripeElement(): void
    {
        $html = $this->renderElement('bfStripe', [
            'dbId' => 53,
            'hideLabel' => true,
            'image' => '',
            'actionClick' => 0,
            'actionFunctionName' => '',
        ]);

        self::assertStringContainsString("'bfPaymentMethod'", $html);
        self::assertStringContainsString("'Stripe'", $html);
    }

    public function testPayPalElement(): void
    {
        $html = $this->renderElement('bfPayPal', [
            'dbId' => 54,
            'hideLabel' => true,
            'image' => '',
            'actionClick' => 0,
            'actionFunctionName' => '',
        ]);

        self::assertStringContainsString("'bfPaymentMethod'", $html);
        self::assertStringContainsString("'PayPal'", $html);
    }

    public function testSofortueberweisungElement(): void
    {
        $html = $this->renderElement('bfSofortueberweisung', [
            'dbId' => 55,
            'hideLabel' => true,
            'image' => '',
            'actionClick' => 0,
            'actionFunctionName' => '',
        ]);

        self::assertStringContainsString("'bfPaymentMethod'", $html);
        self::assertStringContainsString("'Sofortueberweisung'", $html);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function renderElement(string $bfType, array $overrides, string $variant = ''): string
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
        $snapshotVariant = $variant === '' ? '' : '_' . $variant;
        $this->assertMatchesSnapshot('mobile_' . $bfType . $snapshotVariant . '.html', $html);

        return $html;
    }

    private function makeRenderer(): MobileRenderer
    {
        $processor = (new ReflectionClass(HTML_facileFormsProcessor::class))->newInstanceWithoutConstructor();
        $processor->rowcount = 0;
        $processor->rows = [];
        $processor->app = new CMSApplication();
        $processor->form = 27;

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
