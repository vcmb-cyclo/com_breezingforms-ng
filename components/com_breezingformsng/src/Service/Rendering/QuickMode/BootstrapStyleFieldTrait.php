<?php

declare(strict_types=1);

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL - EVH
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Second cut of the shared field-type Strategy layer: these 8 field types
 * are byte-for-byte identical (modulo a trailing blank line, confirmed by
 * diffing the two production files directly) between BootstrapRenderer and
 * OnePageRenderer - both build on the same $this->bsClass()/Bootstrap-5
 * markup convention, unlike Classic (plain HTML) or Mobile (its own
 * simplified markup). Not shared with those two.
 *
 * Each method takes $label and the preamble-computed onclick/onblur/etc and
 * tabIndex/readonly strings as parameters rather than reading them off
 * $this, because they
 * are process()'s local variables, not renderer instance state - a trait
 * has access to $this and its properties, not to the including method's
 * locals.
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\RuntimeAssetLoader;
use Joomla\CMS\Uri\Uri;

\defined('_JEXEC') or die;

trait BootstrapStyleFieldTrait
{
    /**
     * @param array<string, mixed> $mdata
     */
    private function renderBootstrapStyleSummarizeField(array $mdata, string $label): void
    {
        /* translatables */
        if (isset($mdata['emptyMessage_translation' . $this->language_tag]) && $mdata['emptyMessage_translation' . $this->language_tag] != '') {
            $mdata['emptyMessage'] = $mdata['emptyMessage_translation' . $this->language_tag];
        }
        /* translatables end */
        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
        echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('other-form-group') . '">';
        echo $label;
        echo '<span class="' . $this->bsClass('nonform-control') . '">';
        echo '<div style="display: inline-block; vertical-align: top;" class="ff_elem bfSummarize" id="ff_elem' . $mdata['dbId'] . '"></div>' . "\n";
        echo '<script type="text/javascript">bfRegisterSummarize('
            . json_encode('ff_elem' . $mdata['dbId']) . ', '
            . json_encode($mdata['connectWith']) . ', '
            . json_encode($mdata['connectType']) . ', '
            . json_encode($mdata['emptyMessage']) . ', '
            . json_encode((bool) $mdata['hideIfEmpty']) . ');</script>';
        if (trim($mdata['fieldCalc']) != '') {
            echo '<script type="text/javascript">
                                                        <!--
					function bfFieldCalcff_elem' . $mdata['dbId'] . '(value){
						if(!isNaN(value)){
							value = Number(value);
						}
						' . $mdata['fieldCalc'] . '
						return value;
					}
                                                        //-->
					</script>';
        }
        echo '</span>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * @param array<string, mixed> $mdata
     */
    private function renderBootstrapStyleCalendarField(array $mdata, string $label): void
    {
        /* translatables */
        if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
            $mdata['value'] = $mdata['value_translation' . $this->language_tag];
        }
        if (isset($mdata['format_translation' . $this->language_tag]) && $mdata['format_translation' . $this->language_tag] != '') {
            $mdata['format'] = $mdata['format_translation' . $this->language_tag];
        }
        $icon = '';
        if ($this->rootMdata['themebootstrapThemeEngine'] == 'bootstrap' && $this->rootMdata['themebootstrap'] == 'Azure') {
            if (!isset($mdata['icon']) || $mdata['icon'] == '') {
                $icon = '<i class="fas fa-calendar iconf--fumi" aria-hidden="true"></i>';
            } else {
                $icon = '<i class="fas ' . htmlentities($mdata['icon'], ENT_QUOTES, 'UTF-8') . ' iconf--fumi" aria-hidden="true"></i>';
            }
        }
        /* translatables end */
        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
        echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('other-form-group') . '">';
        echo $label;
        echo $icon;
        echo '<span class="' . $this->bsClass('nonform-control') . '">';

        $exploded = explode('::', trim((string) $mdata['value']));
        $left = '';

        if (count($exploded) == 2) {
            $left = trim($exploded[0]);
        } elseif (count($exploded) == 1) {
            $left = trim($exploded[0]);

            if ($left === '...') {
                $left = '';
            }
        }

        $calAttr = [
            'class' => 'ff_elem bfCalendar',
            'showTime' => $this->bfCalendarShowTimeEnabled($mdata),
            'timeFormat' => $this->bfCalendarIsTruthy($mdata, 'timeFormat') ? '24' : '12',
            'singleHeader' => $this->bfCalendarIsTruthy($mdata, 'singleHeader'),
            'todayBtn' => $this->bfCalendarIsTruthy($mdata, 'todayButton'),
            'weekNumbers' => $this->bfCalendarIsTruthy($mdata, 'weekNumbers'),
            'minYear' => (isset($mdata['minYear']) && $mdata['minYear'] != '') ? '-' . $mdata['minYear'] : '',
            'maxYear' => (isset($mdata['maxYear']) && $mdata['maxYear'] != '') ? '+' . $mdata['maxYear'] : '',
            'firstDay' => (isset($mdata['firstDay']) && $mdata['firstDay'] != '') ? $mdata['firstDay'] : '7',
        ];

        echo HTMLHelper::_('calendar', $left, "ff_nm_" . $mdata['bfName'] . "[]", "ff_elem" . $mdata['dbId'], $mdata['format'], $calAttr);

        echo '</span>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * @param array<string, mixed> $mdata
     */
    private function renderBootstrapStyleCheckboxField(array $mdata, string $label, string $tabIndex, string $onclick, string $onblur, string $onchange, string $onfocus, string $onselect, string $readonly): void
    {
        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
        echo '<div class="' . $this->bsClass('form-group') . '">';
        echo $label;
        echo '<span class="' . $this->bsClass('nonform-control') . '">';
        echo '<input class="ff_elem form-check-input" ' . ($mdata['checked'] ? 'checked="checked" ' : '') . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . ($readonly ? ' disabled="disabled" ' : '') . 'type="checkbox" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
        echo '</span>';
        echo '</div>';
        echo '</div>';
        if ($mdata['mailbackAccept']) {
            echo '<input type="hidden" class="ff_elem" name="mailbackConnectWith[' . $mdata['mailbackConnectWith'] . ']" value="true_' . $mdata['bfName'] . '"/>' . "\n";
        }
    }

    /**
     * @param array<string, mixed> $mdata
     */
    private function renderBootstrapStyleSelectField(array $mdata, string $label, string $tabIndex, string $onclick, string $onblur, string $onchange, string $onfocus, string $onselect, string $readonly): void
    {
        /* translatables */
        if (isset($mdata['list_translation' . $this->language_tag]) && $mdata['list_translation' . $this->language_tag] != '') {
            $mdata['list'] = $mdata['list_translation' . $this->language_tag];
        }
        /* translatables end */
        if ($mdata['list'] != '') {
            $width = '';
            if (isset($mdata['width']) && $mdata['width'] != '') {
                $width = 'width:' . htmlentities(strip_tags($mdata['width'])) . ' !important; min-width:' . htmlentities(strip_tags($mdata['width'])) . ' !important;';
            }
            $height = '';
            if (isset($mdata['height']) && $mdata['height'] != '') {
                $height = 'height:' . htmlentities(strip_tags($mdata['height'])) . ';';
            }
            $size = '';
            if ($height != '' || $width != '') {
                $size = 'style="' . $width . $height . '" ';
            }

            $mdata['list'] = str_replace("\r", '', $mdata['list']);
            $gEx = explode("\n", $mdata['list']);
            $lines = count($gEx);
            echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
            echo '<div class="' . $this->bsClass('form-group') . '">';
            echo $label;
            echo '<select data-chosen="no-chzn" class="' . $this->bsClass('form-select') . ' ff_elem chzn-done" ' . $size . ($mdata['multiple'] ? 'multiple="multiple" ' : '') . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '">' . "\n";
            for ($i = 0; $i < $lines; $i++) {
                $iEx = explode(";", $gEx[$i]);
                $iCnt = count($iEx);
                if ($iCnt == 3) {
                    echo '<option ' . ($iEx[0] == 1 ? 'selected="selected" ' : '') . 'value="' . htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8') . '">' . htmlentities(trim($iEx[1]), ENT_QUOTES, 'UTF-8') . '</option>' . "\n";
                }
            }
            echo '</select>' . "\n";
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * @param array<string, mixed> $mdata
     */
    private function renderBootstrapStyleSubmitButtonField(array $mdata, string $label, string $tabIndex, string $onblur, string $onchange, string $onfocus, string $onselect, string $readonly): void
    {
        /* translatables */
        if (isset($mdata['src_translation' . $this->language_tag]) && $mdata['src_translation' . $this->language_tag] != '') {
            $mdata['src'] = $mdata['src_translation' . $this->language_tag];
        }
        if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
            $mdata['value'] = $mdata['value_translation' . $this->language_tag];
        }
        /* translatables end */

        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
        echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('other-form-group') . '">';
        echo $label;
        echo '<span class="' . $this->bsClass('nonform-control') . '">';
        $value = '';
        $type = 'submit';
        $src = '';

        if ($mdata['src'] != '') {
            $type = 'image';
            $src = 'src="' . $mdata['src'] . '" ';
        }
        if ($mdata['value'] != '') {
            $value = 'value="' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '" ';
        }
        if (isset($mdata['actionClick']) && $mdata['actionClick'] == 1) {
            $onclick = 'onclick="if(typeof bf_htmltextareainit != \'undefined\'){ bf_htmltextareainit() }populateSummarizers();if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};' . $mdata['actionFunctionName'] . '(this,\'click\');return false;" ';
        } else {
            $onclick = 'onclick="if(typeof bf_htmltextareainit != \'undefined\'){ bf_htmltextareainit() }populateSummarizers();if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};return false;" ';
        }
        if ($src == '') {
            echo '<button type="button" class="ff_elem ' . $this->bsClass('btn') . ' ' . $this->bsClass('btn-primary') . ' button bfCustomSubmitButton" ' . $value . $src . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '">' . $mdata['value'] . '</button>' . "\n";
        } else {
            echo '<input type="button" class="ff_elem bfCustomSubmitButton" ' . $value . $src . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" alt="" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '" value="' . $mdata['value'] . '"/>' . "\n";
        }
        echo '</span>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * @param array<string, mixed> $mdata
     */
    private function renderBootstrapStylePayPalField(array $mdata, string $label, string $tabIndex, string $onblur, string $onchange, string $onfocus, string $onselect, string $readonly): void
    {
        /* translatables */
        if (isset($mdata['image_translation' . $this->language_tag]) && $mdata['image_translation' . $this->language_tag] != '') {
            $mdata['image'] = $mdata['image_translation' . $this->language_tag];
        }
        /* translatables end */
        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
        echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('other-form-group') . '">';
        echo $label;
        echo '<span class="' . $this->bsClass('nonform-control') . '">';

        $value = '';
        $type = 'submit';
        $src = '';
        if ($mdata['image'] != '') {
            $type = 'image';
            $src = 'src="' . $mdata['image'] . '" alt="PayPal" ';
        } else {
            $value = 'value="PayPal" ';
        }
        if (isset($mdata['actionClick']) && $mdata['actionClick'] == 1) {
            $onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'PayPal\';' . $mdata['actionFunctionName'] . '(this,\'click\');" ';
        } else {
            $onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'PayPal\';" ';
        }
        echo '<input class="ff_elem" ' . $value . $src . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
        echo '</span>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * @param array<string, mixed> $mdata
     */
    private function renderBootstrapStyleSofortueberweisungField(array $mdata, string $label, string $tabIndex, string $onblur, string $onchange, string $onfocus, string $onselect, string $readonly): void
    {
        /* translatables */
        if (isset($mdata['image_translation' . $this->language_tag]) && $mdata['image_translation' . $this->language_tag] != '') {
            $mdata['image'] = $mdata['image_translation' . $this->language_tag];
        }
        /* translatables end */
        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
        echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('other-form-group') . '">';
        echo $label;
        echo '<span class="' . $this->bsClass('nonform-control') . '">';
        $value = '';
        $type = 'submit';
        $src = '';
        if ($mdata['image'] != '') {
            $type = 'image';
            $src = 'src="' . $mdata['image'] . '" alt="Sofort.com" ';
        } else {
            $value = 'value="Sofortueberweisung" ';
        }
        if (isset($mdata['actionClick']) && $mdata['actionClick'] == 1) {
            $onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Sofortueberweisung\';' . $mdata['actionFunctionName'] . '(this,\'click\');" ';
        } else {
            $onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Sofortueberweisung\';" ';
        }
        echo '<input class="ff_elem" ' . $value . $src . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
        echo '</span>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * @param array<string, mixed> $mdata
     */
    private function renderBootstrapStyleSignatureField(array $mdata, string $label): void
    {
        RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/js/signature.js');
        RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-signature.js');
        $this->p->app->getDocument()->getWebAssetManager()->addInlineScript(
            'bfSignatureInit(' . json_encode((int) $mdata['dbId']) . ');'
        );

        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . ' bfSignatureWrap">';
        echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('other-form-group') . '">';
        echo $label;
        echo '<span class="' . $this->bsClass('nonform-control') . '">';

        echo '<div class="bfSignature" id="bfSignature' . $mdata['dbId'] . '"><div class="bfSignatureCanvasBorder"><canvas></canvas></div>' . "\n";
        echo '<button onclick="bfSignatureReset(' . json_encode((int) $mdata['dbId']) . ');" class="bfSignatureResetButton button ' . $this->bsClass('btn') . ' ' . $this->bsClass('btn-primary') . '"><span>' . Text::_('COM_BREEZINGFORMSNG_SIGNATURE_RESET_BUTTON') . '</span></button>' . "\n";
        echo '</div>';
        echo '</span>';
        echo '</div>';
        echo '</div>';
        echo '<input class="ff_elem" type="hidden" name="ff_nm_' . $mdata['bfName'] . '[]" value="" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
    }
}
