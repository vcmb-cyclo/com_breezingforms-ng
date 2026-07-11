<?php
/**
 * BreezingForms NG - A Joomla Forms Application
 * 
 * @version 6.0.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL - EVH
 * @license     Released under the terms of the GNU General Public License
 * */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\Helpers\Bootstrap;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;

require_once JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/functions/helpers.php';

class QuickModeHtml
{
    private static function decodeJsonArray(string $json): array
    {
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    private static function renderSection(string $name, array $vars): void
    {
        extract($vars, EXTR_SKIP);

        require JPATH_ADMINISTRATOR . '/components/com_breezingformsng/layouts/quickmode/' . $name . '.php';
    }

    private static function buildConfigScript(
        string $iconBase,
        string $languageCode,
        int $formId,
        string $formName,
        string $formTitle,
        string $formDesc,
        string $formEmailadr,
        $formEmailntf,
        array $elementScripts,
        string $dataObjectString
    ): string {
        $labelKeys = [
            'COM_BREEZINGFORMSNG_CHOOSE_ONE',
            'COM_BREEZINGFORMSNG_ELEMENTS_CREAACTION',
            'COM_BREEZINGFORMSNG_ELEMENTS_CREAINIT',
            'COM_BREEZINGFORMSNG_ELEMENTS_CREAVALID',
            'COM_BREEZINGFORMSNG_ELEMENTS_EXISTAPP',
            'COM_BREEZINGFORMSNG_ELEMENTS_OLDBELOW',
            'COM_BREEZINGFORMSNG_ERROR_ENTER_LABEL',
            'COM_BREEZINGFORMSNG_ERROR_ENTER_NAME',
            'COM_BREEZINGFORMSNG_ERROR_ENTER_NAME_CHARACTERS',
            'COM_BREEZINGFORMSNG_ERROR_ENTER_TITLE',
            'COM_BREEZINGFORMSNG_ERROR_NAME_EXISTS',
            'COM_BREEZINGFORMSNG_LOAD_PACKAGE',
            'COM_BREEZINGFORMSNG_LOAD_PACKAGE_OF',
            'COM_BREEZINGFORMSNG_NEW_SECTION_ERROR',
            'COM_BREEZINGFORMSNG_NO_CHILDREN_ERROR',
            'COM_BREEZINGFORMSNG_PAGE',
            'COM_BREEZINGFORMSNG_SETTINGS_UPDATED',
        ];

        $labels = [];

        foreach ($labelKeys as $key) {
            $labels[$key] = BFText::_($key);
        }

        $config = [
            'iconBase' => $iconBase,
            'siteRoot' => Uri::root(),
            'siteRootPath' => Uri::root(true),
            'lang' => $languageCode,
            'formId' => $formId,
            'formName' => $formName,
            'formTitle' => $formTitle,
            'formDesc' => str_replace(["\n", "\r"], '', $formDesc),
            'formEmailadr' => $formEmailadr,
            'formEmailntf' => \in_array((int) $formEmailntf, [1, 2], true),
            'elementScripts' => $elementScripts,
            'labels' => $labels,
        ];

        // dataObject is a raw JSON document produced by the legacy exporter; legacy
        // asset paths inside it are rewritten to the current media locations.
        $dataObjectJson = str_replace(
            [
                '..\\/administrator\\/components\\/com_facileforms',
                '..\\/administrator\\/components\\/com_breezingforms\\/libraries\\/jquery\\/themes\\/quickmode\\/i\\/',
                '..\\/administrator\\/components\\/com_breezingformsng\\/libraries\\/jquery\\/themes\\/quickmode\\/i\\/',
                '..\\/administrator\\/components\\/com_breezingforms',
                '..\\/administrator\\/components\\/com_breezingformsng',
            ],
            [
                Uri::root(true) . '\\/administrator\\/components\\/com_breezingformsng',
                Uri::root() . 'media\\/com_breezingformsng\\/images\\/quickmode\\/',
                Uri::root() . 'media\\/com_breezingformsng\\/images\\/quickmode\\/',
                Uri::root(true) . '\\/administrator\\/components\\/com_breezingformsng',
                Uri::root(true) . '\\/administrator\\/components\\/com_breezingformsng',
            ],
            $dataObjectString
        );

        if (trim($dataObjectJson) === '') {
            $dataObjectJson = 'null';
        }

        return 'window.BFQMConfig = ' . json_encode($config)
            . '; window.BFQMConfig.dataObject = ' . $dataObjectJson . ';';
    }

    public static function showApplication($formId, $formName, $formTitle, $formDesc, $formEmailntf, $formEmailadr, $dataObjectString, $elementScripts, $themes, $themesbootstrap, $themesbootstrap3)
    {
        $active_language_code = htmlentities(BFRequest::getVar('active_language_code', ''), ENT_QUOTES, 'UTF-8');
        HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
        HTMLHelper::_('behavior.keepalive');
        $iconBase = Uri::root() . 'media/com_breezingformsng/images/quickmode/';
        $decodedThemeObject = null;
        $decodedThemeObject = self::decodeJsonArray((string) $dataObjectString);
        $isAzureBootstrapTheme = is_array($decodedThemeObject)
            && isset($decodedThemeObject['properties'])
            && is_array($decodedThemeObject['properties'])
            && (($decodedThemeObject['properties']['themebootstrapThemeEngine'] ?? '') === 'bootstrap')
            && (($decodedThemeObject['properties']['themebootstrap'] ?? '') === 'Azure');
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->useStyle('com_breezingformsng.quickmode-style');
        $wa->useStyle('com_breezingformsng.jtree-style');
        $wa->useStyle('com_breezingformsng.admin-style');
        $wa->useStyle('com_breezingformsng.custom-style');
        $wa->useScript('com_breezingformsng.jquery-alias');
        $wa->useScript('com_breezingformsng.jtree');
        $wa->useScript('com_breezingformsng.jq-ui');
        $wa->useScript('com_breezingformsng.base64');
        $wa->useScript('com_breezingformsng.json-plugin');
        $wa->useScript('com_breezingformsng.md5');
        $wa->useScript('com_breezingformsng.center');
        $wa->useScript('com_breezingformsng.scroll');
        $wa->useScript('com_breezingformsng.quickmode-elements');
        $wa->useScript('com_breezingformsng.quickmode-app');
        $wa->useScript('com_breezingformsng.jquery-restore');

        $wa->addInlineScript(self::buildConfigScript(
            $iconBase,
            $active_language_code,
            (int) $formId,
            $formName,
            $formTitle,
            $formDesc,
            $formEmailadr,
            $formEmailntf,
            $elementScripts,
            $dataObjectString
        ));
        ?>

        <?php
        echo Bootstrap::renderModal(
            'bfPreviewModal',
            array(
                'url' => Uri::root() . "index.php?format=html&tmpl=component&option=com_breezingformsng&ff_form=" . $formId . "&ff_page=1",
                'width' => '1024px',
                'height' => '500px'
            )
        );

        echo Bootstrap::renderModal(
            'bfPreviewModal2',
            array(
                'url' => Uri::root() . "index.php?format=html&option=com_breezingformsng&ff_form=" . $formId . "&ff_page=1",
                'width' => '1024px',
                'height' => '500px'
            )
        );
        ?>

        <div style="float:left; margin-right: 3px;">
            <?php ToolbarHelper::custom('save', 'save.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMSNG_TOOLBAR_QUICKMODE_SAVE'), false); ?>
            <?php
            if ($formId != 0) {
                ToolbarHelper::custom('preview', 'publish.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMSNG_TOOLBAR_QUICKMODE_PREVIEW'), false);
                ToolbarHelper::custom('preview_site', 'publish.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMSNG_SITE_PREVIEW'), false);
            }
            ?>
            <?php ToolbarHelper::custom('close', 'cancel.png', 'cancel_f2.png', BFText::_('COM_BREEZINGFORMSNG_TOOLBAR_QUICKMODE_CLOSE'), false); ?>
            <form action="index.php" method="post" name="adminForm" id="adminForm">
                <input type="hidden" name="option" value="com_breezingformsng" />
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="form" value="<?php echo $formId; ?>" />
                <input type="hidden" name="active_language_code" value="<?php echo $active_language_code; ?>" />
                <input type="hidden" name="sizeTplCode" value="0" />
            </form>
        </div>
        <?php
        $default = ComponentHelper::getParams('com_languages')->get('site');

        $showTranslations = $formId > 0
            && count(LanguageHelper::getLanguages()) > 1
            && $active_language_code != ''
            && $active_language_code != $default;
        $wa->addInlineScript(
            'jQuery(document).ready(function () { jQuery(".bfTrans").css("display", "'
            . ($showTranslations ? 'block' : 'none') . '"); });'
        );

        if ($formId > 0 && count(LanguageHelper::getLanguages()) > 1) {
            ?>
            <div onclick="location.href = 'index.php?option=com_breezingformsng&format=html&task=quickmode.display&formName=translationtest&form=<?php echo $formId ?>&active_language_code='"
                class="bfLanguageButton<?php echo $active_language_code == $default || $active_language_code == '' ? ' bfLanguageButtonActive' : '' ?>">
                <?php echo $default; ?>
            </div>
            <?php
            $languages = LanguageHelper::getLanguages();
            foreach ($languages as $language) {
                if ($language->lang_code != $default) {
                    ?>
                    <div onclick="location.href = 'index.php?option=com_breezingformsng&format=html&task=quickmode.display&formName=translationtest&form=<?php echo $formId ?>&active_language_code=<?php echo $language->lang_code; ?>'"
                        class="bfLanguageButton<?php echo $active_language_code == $language->lang_code ? ' bfLanguageButtonActive' : '' ?>">
                        <?php echo $language->lang_code; ?>
                    </div>
                    <?php
                }
            }
        }
        ?>
        <div style="display:none;visibility:hidden;" id="bfSaveQueue"></div>
        <div id="bfQuickModeWrapper" class="bfClearfix">

            <div id="bfQuickModeLeft" class="bfClearfix">

                <form id="newStuffBar" onsubmit="return false;">
                    <button class="btn btn-warning" id="bfNewPageButton">
                        <?php echo BFText::_('COM_BREEZINGFORMSNG_NEW_PAGE'); ?>
                    </button>
                    <button class="btn btn-warning" id="bfNewSectionButton">
                        <?php echo BFText::_('COM_BREEZINGFORMSNG_NEW_SECTION'); ?>
                    </button>
                    <button class="btn btn-warning" id="bfNewElementButton">
                        <?php echo BFText::_('COM_BREEZINGFORMSNG_NEW_ELEMENT'); ?>
                    </button>
                </form>
                <input id="scrollElementList" type="checkbox"><label for="scrollElementList"
                    class="scrollElementListLabel">Scroll element list</label>
                <div id="bfElementExplorer"></div>

            </div> <!-- ##### bfQuickModeLeft end ##### -->


            <div id="bfQuickModeRight" class="bfClearfix">

                <form name="bfForm" onsubmit="return false">

                    <div id="menutab" class="flora">
                        <input id="triggerScrollable" type="checkbox"><label class="triggerScrollableLabel"
                            for="triggerScrollable">Keep panel docked</label>
                        <ul>
                            <li><a onclick="JQuery('.bfFadingMessage').css('display', 'none')" href="#fragment-1"><span>
                                        <div class="tab-items">
                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_PROPERTIES') ?>
                                        </div>
                                    </span></a>
                            </li>
                            <li><a onclick="JQuery('.bfFadingMessage').css('display', 'none')" href="#fragment-2"><span>
                                        <div class="tab-element">
                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_ADVANCED') ?>
                                        </div>
                                    </span></a>
                            </li>
                        </ul>

                        <div class="t">

                            <div class="t">
                                <div class="t"></div>
                            </div>
                        </div>

                        <div class="m">

                            <div id="fragment-1">
                                <div>
                                    <br />
                                    <div class="bfFadingMessage" style="display:none"></div>
                                    <input type="submit" class="btn btn-secondary"
                                        value="<?php echo BFText::_('COM_BREEZINGFORMSNG_PROPERTIES_SAVE'); ?>"
                                    <?php self::renderSection('properties_form', get_defined_vars()); ?>
                                    <?php self::renderSection('properties_page', get_defined_vars()); ?>
                                    <?php self::renderSection('properties_section', get_defined_vars()); ?>
                                    <?php self::renderSection('properties_element', get_defined_vars()); ?>
                                    <!-- ELEMENT PROPERTIES END -->
                                    <div class="bfFadingMessage" style="display:none"></div>
                                    <input type="submit" class="btn btn-secondary"
                                        value="<?php echo BFText::_('COM_BREEZINGFORMSNG_PROPERTIES_SAVE'); ?>"
                                        id="bfPropertySaveButton" />
                                    <br />
                                    <br />

                                </div>
                            </div>

                            <div id="fragment-2">
                                <div>
                                    <br />

                                    <div class="bfFadingMessage" style="display:none"></div>
                                    <input type="submit" class="btn btn-secondary"
                                        value="<?php echo BFText::_('COM_BREEZINGFORMSNG_PROPERTIES_SAVE'); ?>"
                                        id="bfAdvancedSaveButtonTop" />
                                    <?php self::renderSection('advanced_form', get_defined_vars()); ?>
                                    <?php self::renderSection('advanced_element', get_defined_vars()); ?>
                                    <br />
                                    <div class="bfFadingMessage" style="display:none"></div>
                                    <input type="submit" class="btn btn-secondary"
                                        value="<?php echo BFText::_('COM_BREEZINGFORMSNG_PROPERTIES_SAVE'); ?>"
                                        id="bfAdvancedSaveButton" />
                                    <br />
                                    <br />

                                </div>
                            </div>
                        </div>


                        <div class="b">
                            <div class="b">
                                <div class="b"></div>
                            </div>
                        </div>
                    </div>

                </form>

            </div> <!-- ##### bfQuickModeRight end ##### -->

        </div> <!-- ##### bfQuickModeWrapper end ##### -->

        <?php
    }

    public static function getContent($editor)
    {

        return 'Joomla.editors.instances[' . json_encode($editor) . '].getValue()';
    }

    public static function setContent($editor, $content)
    {

        return 'Joomla.editors.instances[' . json_encode($editor) . '].setValue(' . json_encode($content) . ')';
    }

    public static function renderCodeMirror($name)
    {

        $params = array('syntax' => 'javascript');
        $editor = Editor::getInstance('codemirror');

        // Element id is mandatory for CodeMirror v6.
        return $editor->display($name, '', '100%', 300, 40, 20, false, $name, null, null, $params);
    }

}
