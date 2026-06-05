<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 5.0
 * @package     BreezingForms
 * @copyright   (C) 2008-2020 by Markus Bopp
 * @copyright   (C) 2024 by XDA+GIL - EVH
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

class QuickModeHtml
{

    public static function showApplication($formId, $formName, $formTitle, $formDesc, $formEmailntf, $formEmailadr, $dataObjectString, $elementScripts, $themes, $themesbootstrap, $themesbootstrap3)
    {
        $active_language_code = htmlentities(BFRequest::getVar('active_language_code', ''), ENT_QUOTES, 'UTF-8');
        HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
        HTMLHelper::_('behavior.keepalive');
        $iconBase = Uri::root() . 'media/com_breezingformsng/images/quickmode/';
        $decodedThemeObject = null;
        try {
            $decodedThemeObject = Zend_Json::decode($dataObjectString);
        } catch (\Exception $e) {
            $decodedThemeObject = null;
        }
        $isAzureBootstrapTheme = is_array($decodedThemeObject)
            && isset($decodedThemeObject['properties'])
            && is_array($decodedThemeObject['properties'])
            && (($decodedThemeObject['properties']['themebootstrapThemeEngine'] ?? '') === 'bootstrap')
            && (($decodedThemeObject['properties']['themebootstrap'] ?? '') === 'Azure');
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->useStyle('com_breezingformsng.quickmode-style');
        $wa->useStyle('com_breezingformsng.jtree-style');
        $wa->useStyle('com_breezingformsng.admin-style');
        $wa->useScript('com_breezingformsng.jquery-alias');
        $wa->useScript('com_breezingformsng.jtree');
        $wa->useScript('com_breezingformsng.jq-ui');
        $wa->useScript('com_breezingformsng.base64');
        $wa->useScript('com_breezingformsng.json-plugin');
        $wa->useScript('com_breezingformsng.md5');
        $wa->useScript('com_breezingformsng.center');
        $wa->useScript('com_breezingformsng.scroll');
        ?>

        <?php
        $wa->useScript('com_breezingformsng.quickmode-elements');
        $wa->useScript('com_breezingformsng.quickmode-app');
        ?>

        <script type="text/javascript">
        window.BFQMConfig = {
            iconBase:      '<?php echo $iconBase; ?>',
            siteRoot:      '<?php echo Uri::root(); ?>',
            siteRootPath:  '<?php echo Uri::root(true); ?>',
            lang:          '<?php echo $active_language_code; ?>',
            formId:        <?php echo (int)$formId; ?>,
            formName:      '<?php echo addslashes($formName); ?>',
            formTitle:     '<?php echo addslashes($formTitle); ?>',
            formDesc:      '<?php echo addslashes(str_replace(["\n", "\r"], ['', ''], $formDesc)); ?>',
            formEmailadr:  '<?php echo addslashes($formEmailadr); ?>',
            formEmailntf:  <?php echo (addslashes($formEmailntf) == 2 || addslashes($formEmailntf) == 1) ? 'true' : 'false'; ?>,
            elementScripts: <?php echo Zend_Json::encode($elementScripts); ?>,
            dataObject:    <?php echo str_replace(
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
            ); ?>,
            labels: {
            "COM_BREEZINGFORMSNG_CHOOSE_ONE": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_CHOOSE_ONE')); ?>',
            "COM_BREEZINGFORMSNG_ELEMENTS_CREAACTION": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_CREAACTION')); ?>',
            "COM_BREEZINGFORMSNG_ELEMENTS_CREAINIT": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_CREAINIT')); ?>',
            "COM_BREEZINGFORMSNG_ELEMENTS_CREAVALID": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_CREAVALID')); ?>',
            "COM_BREEZINGFORMSNG_ELEMENTS_EXISTAPP": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_EXISTAPP')); ?>',
            "COM_BREEZINGFORMSNG_ELEMENTS_OLDBELOW": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_OLDBELOW')); ?>',
            "COM_BREEZINGFORMSNG_ERROR_ENTER_LABEL": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_ERROR_ENTER_LABEL')); ?>',
            "COM_BREEZINGFORMSNG_ERROR_ENTER_NAME": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_ERROR_ENTER_NAME')); ?>',
            "COM_BREEZINGFORMSNG_ERROR_ENTER_NAME_CHARACTERS": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_ERROR_ENTER_NAME_CHARACTERS')); ?>',
            "COM_BREEZINGFORMSNG_ERROR_ENTER_TITLE": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_ERROR_ENTER_TITLE')); ?>',
            "COM_BREEZINGFORMSNG_ERROR_NAME_EXISTS": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_ERROR_NAME_EXISTS')); ?>',
            "COM_BREEZINGFORMSNG_LOAD_PACKAGE": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_LOAD_PACKAGE')); ?>',
            "COM_BREEZINGFORMSNG_LOAD_PACKAGE_OF": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_LOAD_PACKAGE_OF')); ?>',
            "COM_BREEZINGFORMSNG_NEW_SECTION_ERROR": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_NEW_SECTION_ERROR')); ?>',
            "COM_BREEZINGFORMSNG_NO_CHILDREN_ERROR": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_NO_CHILDREN_ERROR')); ?>',
            "COM_BREEZINGFORMSNG_PAGE": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_PAGE')); ?>',
            "COM_BREEZINGFORMSNG_SETTINGS_UPDATED": '<?php echo addslashes(BFText::_('COM_BREEZINGFORMSNG_SETTINGS_UPDATED')); ?>'
            }
        };
        </script>

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
                <input type="hidden" name="act" value="quickmode" />
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="form" value="<?php echo $formId; ?>" />
                <input type="hidden" name="active_language_code" value="<?php echo $active_language_code; ?>" />
                <input type="hidden" name="sizeTplCode" value="0" />
            </form>
        </div>
        <?php
        $menutabcss = 'width: 100%;';
        $menutabcss = 'width: 100%;';
        ?>
        <style type="text/css">
            #bfQuickModeRight #menutab {
                <?php echo $menutabcss; ?>
            }
        </style>
        <?php 
        $default = ComponentHelper::getParams('com_languages')->get('site');

        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript('
        jQuery(document).ready(function () {
            jQuery(\'.bfTrans\').css("display", "none");
        });
        ');
        if ($formId > 0 && count(LanguageHelper::getLanguages()) > 1) {
            if ($active_language_code != '' && $active_language_code != $default) {
                Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript('
                jQuery(document).ready(function () {
                    jQuery(\'.bfTrans\').css("display", "block");
                });
                ');
            }
            ?>
            <div onclick="location.href = 'index.php?option=com_breezingformsng&format=html&act=manageforms&task=quickmode&formName=translationtest&form=<?php echo $formId ?>&active_language_code='"
                class="bfLanguageButton<?php echo $active_language_code == $default || $active_language_code == '' ? ' bfLanguageButtonActive' : '' ?>">
                <?php echo $default; ?>
            </div>
            <?php
            $languages = LanguageHelper::getLanguages();
            foreach ($languages as $language) {
                if ($language->lang_code != $default) {
                    ?>
                    <div onclick="location.href = 'index.php?option=com_breezingformsng&format=html&act=manageforms&task=quickmode&formName=translationtest&form=<?php echo $formId ?>&active_language_code=<?php echo $language->lang_code; ?>'"
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
                                        id="bfPropertySaveButtonTop" />
                                    <!-- FORM PROPERTIES BEGIN -->
                                    <div class="bfProperties" id="bfFormProperties" style="display:none">
                                        <br />
                                        <fieldset>

                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_PROPERTIES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_TITLE')); ?>"
                                                    for="bfFormTitle">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_TITLE'); ?>
                                                </label>
                                                <input type="text"
                                                    value="<?php echo htmlentities($formTitle, ENT_QUOTES, 'UTF-8') ?>"
                                                    id="bfFormTitle" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_TITLE')); ?>"
                                                    for="bfFormTitleTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_TITLE'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="" id="bfFormTitleTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_NAME')); ?>"
                                                    for="bfFormName">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_NAME'); ?>
                                                </label>
                                                <input type="text"
                                                    value="<?php echo htmlentities($formName, ENT_QUOTES, 'UTF-8') ?>"
                                                    id="bfFormName" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_DESCRIPTION')); ?>"
                                                    for="bfFormDescription">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_DESC'); ?>
                                                </label>
                                                <textarea
                                                    id="bfFormDescription"><?php echo htmlentities($formDesc, ENT_QUOTES, 'UTF-8') ?></textarea>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_LASTPAGE')); ?>"
                                                    for="bfFormLastPageThankYou">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_LAST_PAGE_THANK_YOU'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfFormLastPageThankYou" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_EMAIL_NOTIFICATION')); ?>""
                                                       for=" bfFormMailNotification">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_MAIL_NOTIFICATION'); ?>
                                                </label>
                                                <input <?php echo $formEmailntf == 2 || $formEmailntf == 1 ? 'checked="checked"' : '' ?> type="checkbox"
                                                    value="<?php echo htmlentities($formEmailntf, ENT_QUOTES, 'UTF-8') ?>"
                                                    id="bfFormMailNotification" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_EMAIL_NOTIFICATION_ADDRESS')); ?>"
                                                    for="bfFormMailRecipient">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_MAIL_RECIPIENT'); ?>
                                                </label>
                                                <input type="text"
                                                    value="<?php echo htmlentities($formEmailadr, ENT_QUOTES, 'UTF-8') ?>"
                                                    id="bfFormMailRecipient" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_SUBMIT_INCLUDE')); ?>"
                                                    for="bfSubmitIncludeYes">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_SUBMIT_INCLUDE'); ?>
                                                </label>
                                                <input checked="checked" type="radio" name="bfSubmitInclude" value=""
                                                    id="bfSubmitIncludeYes" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_YES'); ?>
                                                <input type="radio" name="bfSubmitInclude" value="" id="bfSubmitIncludeNo" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_NO'); ?>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_SUBMIT_LABEL')); ?>"
                                                    for="bfFormSubmitLabel">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_SUBMIT_LABEL'); ?>
                                                </label>
                                                <input type="text" value="save" id="bfFormSubmitLabel" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_SUBMIT_LABEL')); ?>"
                                                    for="bfFormSubmitLabelTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_SUBMIT_LABEL'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="save" id="bfFormSubmitLabelTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_PAGING_INCLUDE')); ?>"
                                                    for="bfPagingIncludeYes">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_PAGING_INCLUDE'); ?>
                                                </label>
                                                <input checked="checked" type="radio" name="bfPagingInclude" value=""
                                                    id="bfPagingIncludeYes" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_YES'); ?>
                                                <input type="radio" name="bfPagingInclude" value="" id="bfPagingIncludeNo" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_NO'); ?>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_PAGING_NEXT_LABEL')); ?>"
                                                    for="bfFormPagingNextLabel">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_PAGING_NEXT_LABEL'); ?>
                                                </label>
                                                <input type="text" value="next" id="bfFormPagingNextLabel" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_PAGING_NEXT_LABEL')); ?>"
                                                    for="bfFormPagingNextLabelTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_PAGING_NEXT_LABEL'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="next" id="bfFormPagingNextLabelTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_PAGING_PREV_LABEL')); ?>"
                                                    for="bfFormPagingPrevLabel">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_PAGING_PREV_LABEL'); ?>
                                                </label>
                                                <input type="text" value="back" id="bfFormPagingPrevLabel" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_PAGING_PREV_LABEL')); ?>"
                                                    for="bfFormPagingPrevLabelTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_PAGING_PREV_LABEL'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="back" id="bfFormPagingPrevLabelTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_CANCEL_INCLUDE')); ?>"
                                                    for="bfCancelIncludeYes">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_CANCEL_INCLUDE'); ?>
                                                </label>
                                                <input checked="checked" type="radio" name="bfCancelInclude" value=""
                                                    id="bfCancelIncludeYes" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_YES'); ?>
                                                <input type="radio" name="bfCancelInclude" value="" id="bfCancelIncludeNo" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_NO'); ?>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_CANCEL_LABEL')); ?>"
                                                    for="bfFormCancelLabel">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_CANCEL_LABEL'); ?>
                                                </label>
                                                <input type="text" value="reset" id="bfFormCancelLabel" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FORM_CANCEL_LABEL')); ?>"
                                                    for="bfFormCancelLabelTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_CANCEL_LABEL'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="reset" id="bfFormCancelLabelTrans" />
                                            </div>

                                        </fieldset>
                                    </div>
                                    <!-- FORM PROPERTIES END -->

                                    <!-- PAGE PROPERTIES BEGIN -->
                                    <div class="bfProperties" id="bfPageProperties" style="display:none">
                                        <br />
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_PAGE_PROPERTIES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAGE_INTRO')); ?>"
                                                    for="bfPageIntro">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_PAGE_INTRO'); ?>
                                                </label>

                                                <?php
                                                echo Bootstrap::renderModal(
                                                    'bfPropertiesIntroModal',
                                                    array(
                                                        'url' => "index.php?option=com_breezingformsng&tmpl=component&act=quickmode_editor",
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a href="javascript:jQuery('#bfPropertiesIntroModal').modal('show');void(0);"
                                                    data-toggle="modal">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_EDIT_INTRO'); ?>
                                                </a>

                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAGE_INTRO')); ?>"
                                                    for="bfPageIntroTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_PAGE_INTRO'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>


                                                <?php
                                                echo Bootstrap::renderModal(
                                                    'bfPropertiesIntroModal2',
                                                    array(
                                                        'url' => "index.php?option=com_breezingformsng&tmpl=component&act=quickmode_editor&active_language_code=" . $active_language_code,
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a href="javascript:jQuery('#bfPropertiesIntroModal2').modal('show');void(0);"
                                                    data-toggle="modal">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_EDIT_INTRO'); ?>
                                                </a>

                                            </div>
                                        </fieldset>
                                    </div>
                                    <!-- PAGE PROPERTIES END -->

                                    <!-- SECTION PROPERTIES BEGIN -->
                                    <div class="bfProperties" id="bfSectionProperties" style="display:none">
                                        <br />
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_PROPERTIES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_TYPE')); ?>"
                                                    for="bfSectionType">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_TYPE'); ?>
                                                </label>
                                                <select id="bfSectionType">
                                                    <option value="normal">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_NORMAL'); ?>
                                                    </option>
                                                    <option value="section">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FIELDSET'); ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_DISPLAY_TYPE')); ?>"
                                                    for="bfSectionDisplayType">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_DISPLAY_TYPE'); ?>
                                                </label>
                                                <select id="bfSectionDisplayType">
                                                    <option value="inline">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_INLINE'); ?>
                                                    </option>
                                                    <option value="breaks">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_BREAKS'); ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_TITLE')); ?>"
                                                    for="bfSectionTitle">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_TITLE'); ?>
                                                </label>
                                                <input type="text" value="" id="bfSectionTitle" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_TITLE')); ?>"
                                                    for="bfSectionTitleTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_TITLE'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="" id="bfSectionTitleTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_NAME')); ?>"
                                                    for="bfSectionName">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_NAME'); ?>
                                                </label>
                                                <input type="text" value="" id="bfSectionName" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_DESCRIPTION')); ?>"
                                                    for="bfSectionDescription">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_DESCRIPTION'); ?>
                                                </label>


                                                <?php
                                                echo Bootstrap::renderModal(
                                                    'bfPropertiesDescriptionModal',
                                                    array(
                                                        'url' => "index.php?option=com_breezingformsng&tmpl=component&act=quickmode_editor",
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a
                                                    href="javascript:jQuery('#bfPropertiesDescriptionModal').modal('show');void(0);">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_EDIT_DESCRIPTION'); ?>
                                                </a>

                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_DESCRIPTION')); ?>"
                                                    for="bfSectionDescriptionTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_DESCRIPTION'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>

                                                <?php
                                                echo Bootstrap::renderModal(
                                                    'bfPropertiesDescriptionModal2',
                                                    array(
                                                        'url' => "index.php?option=com_breezingformsng&tmpl=component&act=quickmode_editor&active_language_code=" . $active_language_code,
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a
                                                    href="javascript:jQuery('#bfPropertiesDescriptionModal').modal('show');void(0);">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_EDIT_DESCRIPTION'); ?>
                                                </a>


                                            </div>
                                        </fieldset>
                                    </div>
                                    <!-- SECTION PROPERTIES END -->

                                    <!-- ELEMENT PROPERTIES BEGIN -->
                                    <div class="bfProperties" id="bfElementProperties" style="display:none">
                                        <br />
                                        <fieldset>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TYPE')); ?>"
                                                    for="bfElementType">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_TYPE'); ?>
                                                </label>
                                                <select id="bfElementType">
                                                    <option value="">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_CHOOSE_ONE'); ?>
                                                    </option>
                                                    <?php
                                                    $elementTypeOptions = array(
                                                        array('value' => 'bfElementTypeCalendar', 'label' => BFText::_('COM_BREEZINGFORMSNG_CALENDAR'), 'icon' => '📅'),
                                                        array('value' => 'bfElementTypeCalendarResponsive', 'label' => BFText::_('COM_BREEZINGFORMSNG_CALENDAR_RESPONSIVE'), 'icon' => '📆'),
                                                        array('value' => 'bfElementTypeCaptcha', 'label' => BFText::_('COM_BREEZINGFORMSNG_CAPTCHA'), 'icon' => '🔐'),
                                                        array('value' => 'bfElementTypeCheckbox', 'label' => BFText::_('COM_BREEZINGFORMSNG_CHECKBOX'), 'icon' => '☑️'),
                                                        array('value' => 'bfElementTypeCheckboxGroup', 'label' => BFText::_('COM_BREEZINGFORMSNG_CHECKBOX_GROUP'), 'icon' => '✅'),
                                                        array('value' => 'bfElementTypeFile', 'label' => BFText::_('COM_BREEZINGFORMSNG_FILE'), 'icon' => '📎'),
                                                        array('value' => 'bfElementTypeHidden', 'label' => BFText::_('COM_BREEZINGFORMSNG_HIDDEN'), 'icon' => '🙈'),
                                                        array('value' => 'bfElementTypeNumberInput', 'label' => BFText::_('COM_BREEZINGFORMSNG_NUMBER_INPUT'), 'icon' => '🔢'),
                                                        array('value' => 'bfElementTypePayPal', 'label' => BFText::_('COM_BREEZINGFORMSNG_PAYPAL'), 'icon' => '💳'),
                                                        array('value' => 'bfElementTypeRadioGroup', 'label' => BFText::_('COM_BREEZINGFORMSNG_RADIO_GROUP'), 'icon' => '🔘'),
                                                        array('value' => 'bfElementTypeReCaptcha', 'label' => BFText::_('COM_BREEZINGFORMSNG_ReCaptcha'), 'icon' => '🛡️'),
                                                        array('value' => 'bfElementTypeSelect', 'label' => BFText::_('COM_BREEZINGFORMSNG_SELECT'), 'icon' => '📋'),
                                                        array('value' => 'bfElementTypeSignature', 'label' => BFText::_('COM_BREEZINGFORMSNG_SIGNATURE'), 'icon' => '✍️'),
                                                        array('value' => 'bfElementTypeSlider', 'label' => 'Slider', 'icon' => '🎚️'),
                                                        array('value' => 'bfElementTypeSofortueberweisung', 'label' => BFText::_('COM_BREEZINGFORMSNG_SOFORTUEBERWEISUNG'), 'icon' => '🏦'),
                                                        array('value' => 'bfElementTypeStripe', 'label' => BFText::_('COM_BREEZINGFORMSNG_STRIPE'), 'icon' => '💳'),
                                                        array('value' => 'bfElementTypeSubmitButton', 'label' => BFText::_('COM_BREEZINGFORMSNG_SUBMIT_BUTTON'), 'icon' => '🚀'),
                                                        array('value' => 'bfElementTypeSummarize', 'label' => BFText::_('COM_BREEZINGFORMSNG_SUMMARIZE'), 'icon' => '🧮'),
                                                        array('value' => 'bfElementTypeTextarea', 'label' => BFText::_('COM_BREEZINGFORMSNG_TEXTAREA'), 'icon' => '📝'),
                                                        array('value' => 'bfElementTypeText', 'label' => BFText::_('COM_BREEZINGFORMSNG_TEXTFIELD'), 'icon' => '🔤')
                                                    );

                                                    usort($elementTypeOptions, function ($a, $b) {
                                                        return strcasecmp($a['label'], $b['label']);
                                                    });

                                                    foreach ($elementTypeOptions as $elementTypeOption) :
                                                        $optionText = $elementTypeOption['icon'] . ' ' . $elementTypeOption['label'];
                                                    ?>
                                                        <option value="<?php echo $elementTypeOption['value']; ?>">
                                                            <?php echo htmlspecialchars($optionText, ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_PROPERTIES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LABEL')); ?>"
                                                    for="bfElementLabel">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_LABEL'); ?>
                                                </label>
                                                <input type="text" value="" id="bfElementLabel" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LABEL')); ?>"
                                                    for="bfElementLabelTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_LABEL'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="" id="bfElementLabelTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_NAME')); ?>"
                                                    for="bfElementName">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_NAME'); ?>
                                                </label>
                                                <input type="text" value="" id="bfElementName" />
                                            </div>

                                            <!-- NUMBER INPUT BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeNumberInput" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip" title="<?php echo 'value'; ?>"
                                                        for="bfElementTypeNumberInputValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeNumberInputValue" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo 'placeholder'; ?>"
                                                        for="bfElementTypeNumberInputPlaceholder">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_TEXT_PLACEHOLDER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeNumberInputPlaceholder" />
                                                </div>
                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo 'translated placholder'; ?>"
                                                        for="bfElementTypeNumberInputPlaceholderTrans">
                                                        <?php echo 'translated placeholder'; ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeNumberInputPlaceholderTrans" />
                                                </div>
                                                <?php
                                                // Icon for Textfield
                                                // This code is only for NEW THEME

                                                if ($isAzureBootstrapTheme) {
                                                    ?>

                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo 'icon element'; ?>"
                                                            for="bfElementTypeNumberInputIcon">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_ICON'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementTypeNumberInputIcon" />
                                                    </div>
                                                <?php } ?>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_SIZE')); ?>"
                                                        for="bfElementTypeNumberInputSize">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_SIZE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeNumberInputSize" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_MAXLENGTH')); ?>"
                                                        for="bfElementTypeNumberInputMaxLength">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_MAX_LENGTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeNumberInputMaxLength" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_HINT')); ?>"
                                                        for="bfElementTypeNumberInputHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeNumberInputHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_HINT')); ?>"
                                                        for="bfElementTypeNumberInputHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeNumberInputHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- NUMBER INPUT END -->

                                            <!-- HIDDEN BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeHidden" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDDEN_VALUE')); ?>"
                                                        for="bfElementTypeHiddenValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeHiddenValue" />
                                                </div>
                                            </div>
                                            <!-- HIDDEN END -->
                                            <!-- SUMMARIZE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSummarize" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_SUMMARIZE_CONNECTWITH')); ?>"
                                                        for="bfElementTypeSummarizeConnectWith">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_SUMMARIZE_CONNECT_WITH'); ?>
                                                    </label>
                                                    <select id="bfElementTypeSummarizeConnectWith">
                                                        <option value="">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_CHOOSE_ONE'); ?>
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_SUMMARIZE_EMPTY_MESSAGE')); ?>"
                                                        for="bfElementTypeSummarizeEmptyMessage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_EMPTY_MESSAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSummarizeEmptyMessage" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_SUMMARIZE_EMPTY_MESSAGE')); ?>"
                                                        for="bfElementTypeSummarizeEmptyMessageTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_EMPTY_MESSAGE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSummarizeEmptyMessageTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_SUMMARIZE_EMPTY_HIDE')); ?>"
                                                        for="bfElementTypeSummarizeHideIfEmpty">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HIDE_EMPTY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeSummarizeHideIfEmpty" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_SUMMARIZE_USE_LABEL')); ?>"
                                                        for="bfElementTypeSummarizeUseElementLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_USE_LABEL'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementTypeSummarizeUseElementLabel" />
                                                </div>
                                            </div>
                                            <!-- SUMMARIZE END -->
                                            <!-- TEXTFIELD BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeText" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_VALUE')); ?>"
                                                        for="bfElementTypeTextValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextValue" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_VALUE')); ?>"
                                                        for="bfElementTypeTextValueTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextValueTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_PLACEHOLDER')); ?>"
                                                        for="bfElementTypeTextPlaceholder">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_TEXT_PLACEHOLDER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextPlaceholder" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_PLACEHOLDER')); ?>"
                                                        for="bfElementTypeTextPlaceholderTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_TEXT_PLACEHOLDER'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextPlaceholderTrans" />
                                                </div>
                                                <?php
                                                // Icon for Textfield
                                                // This code is only for NEW THEME

                                                if ($isAzureBootstrapTheme) {
                                                    ?>

                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ICON')); ?>"
                                                            for="bfElementTypeTextIcon">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_ICON'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementTypeTextIcon" />
                                                    </div>

                                                <?php } ?>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_SIZE')); ?>"
                                                        for="bfElementTypeTextSize">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_SIZE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextSize" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_MAXLENGTH')); ?>"
                                                        for="bfElementTypeTextMaxLength">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_MAX_LENGTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextMaxLength" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_HINT')); ?>"
                                                        for="bfElementTypeTextHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeTextHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTFIELD_HINT')); ?>"
                                                        for="bfElementTypeTextHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeTextHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- TEXTFIELD END -->
                                            <!-- TEXTAREA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeTextarea" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTAREA_VALUE')); ?>"
                                                        for="bfElementTypeTextareaValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeTextareaValue"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTAREA_VALUE')); ?>"
                                                        for="bfElementTypeTextareaValueTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeTextareaValueTrans"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTAREA_PLACEHOLDER')); ?>"
                                                        for="bfElementTypeTextareaPlaceholder">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_TEXT_PLACEHOLDER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextareaPlaceholder" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTAREA_PLACEHOLDER')); ?>"
                                                        for="bfElementTypeTextareaPlaceholderTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_TEXT_PLACEHOLDER'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextareaPlaceholderTrans" />
                                                </div>
                                                <?php
                                                // Icon for Textarea
                                                // This code is only for NEW THEME

                                                if ($isAzureBootstrapTheme) {
                                                    ?>

                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ICON')); ?>"
                                                            for="bfElementTypeTextareaIcon">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_ICON'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementTypeTextareaIcon" />
                                                    </div>

                                                <?php } ?>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTAREA_WIDTH')); ?>"
                                                        for="bfElementTypeTextareaWidth">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_WIDTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextareaWidth" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTAREA_HEIGHT')); ?>"
                                                        for="bfElementTypeTextareaHeight">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HEIGHT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextareaHeight" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTAREA_MAXLENGTH')); ?>"
                                                        for="bfElementTypeTextareaMaxLength">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_MAX_LENGTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextareaMaxLength" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTAREA_MAXLENGTH_SHOW')); ?>"
                                                        for="bfElementTypeTextareaMaxLengthShow">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_MAX_LENGTH_SHOW'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeTextareaMaxLengthShow" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TEXTAREA_HTML')); ?>"
                                                        for="bfElementTypeTextareaIsHtml">HTML</label>
                                                    <input type="checkbox" value="" id="bfElementTypeTextareaIsHtml" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeTextareaHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeTextareaHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeTextareaHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeTextareaHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- TEXTAREA END -->

                                            <!-- RADIOGROUP BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeRadioGroup" style="display:none">

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_RADIO_GROUP')); ?>"
                                                        for="bfElementTypeRadioGroupGroups">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_GROUP'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeRadioGroupGroups"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_RADIO_GROUP')); ?>"
                                                        for="bfElementTypeRadioGroupGroupsTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_GROUP'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeRadioGroupGroupsTrans"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_RADIO_GROUP_READONLY')); ?>"
                                                        for="bfElementTypeRadioGroupReadonly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeRadioGroupReadonly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_RADIO_GROUP_WRAP')); ?>"
                                                        for="bfElementTypeRadioGroupWrap">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_WRAP'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeRadioGroupWrap" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeRadioGroupHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeRadioGroupHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeRadioGroupHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeRadioGroupHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- RADIOGROUP END -->
                                            <!-- SUBMITBUTTON BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSubmitButton" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SUBMIT_VALUE')); ?>"
                                                        for="bfElementTypeSubmitButtonValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSubmitButtonValue" />
                                                </div>


                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SUBMIT_VALUE')); ?>"
                                                        for="bfElementTypeSubmitButtonValueTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSubmitButtonValueTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSubmitButtonHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeSubmitButtonHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSubmitButtonHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeSubmitButtonHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- SUBMITBUTTON END -->


                                            <!-- STRIPE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeStripe" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_SECRETKEY')); ?>"
                                                        for="bfElementTypeStripeSecretKey">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_SECRETKEY'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripeSecretKey" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_PUBLISHABLEKEY')); ?>"
                                                        for="bfElementTypeStripePublishableKey">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_PUBLISHABLEKEY'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripePublishableKey" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_ITEMNAME')); ?>"
                                                        for="bfElementTypeStripeItemname">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_ITEMNAME'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripeItemname" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_AMOUNT')); ?>"
                                                        for="bfElementTypeStripeAmount">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_AMOUNT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripeAmount" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_CURRENCY')); ?>"
                                                        for="bfElementTypeStripeCurrencyCode">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_CURRENCY_CODE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripeCurrencyCode" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_THANKYOUPAGE')); ?>"
                                                        for="bfElementTypeStripeThankYouPage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_THANKYOU_PAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripeThankYouPage" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_SENDNOTAFPAY')); ?>"
                                                        for="bfElementTypeStripeSendNotificationAfterPayment">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_NOTIFICATION_AFTER_PAYMENT'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementTypeStripeSendNotificationAfterPayment" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeStripeHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeStripeHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeStripeHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeStripeHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- STRIPE END -->


                                            <!-- PAYPAL BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypePayPal" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_BUSINESS')); ?>"
                                                        for="bfElementTypePayPalBusiness">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_BUSINESS'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalBusiness" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_TOKEN')); ?>"
                                                        for="bfElementTypePayPalToken">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_TOKEN'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalToken" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_ITEMNAME')); ?>"
                                                        for="bfElementTypePayPalItemname">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_ITEMNAME'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalItemname" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_ITEMNUMBER')); ?>"
                                                        for="bfElementTypePayPalItemnumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_ITEMNUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalItemnumber" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_AMOUNT')); ?>"
                                                        for="bfElementTypePayPalAmount">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_AMOUNT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalAmount" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_TAX')); ?>"
                                                        for="bfElementTypePayPalTax">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_TAX'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalTax" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_THANKYOUPAGE')); ?>"
                                                        for="bfElementTypePayPalThankYouPage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_THANKYOU_PAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalThankYouPage" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_CANCEL')); ?>"
                                                        for="bfElementTypePayPalCancelURL">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_CANCEL_URL'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalCancelURL" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_LOCALE')); ?>"
                                                        for="bfElementTypePayPalLocale">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_LOCALE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalLocale" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_CURRENCY')); ?>"
                                                        for="bfElementTypePayPalCurrencyCode">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_CURRENCY_CODE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalCurrencyCode" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_SENDNOTAFPAY')); ?>"
                                                        for="bfElementTypePayPalSendNotificationAfterPayment">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_NOTIFICATION_AFTER_PAYMENT'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementTypePayPalSendNotificationAfterPayment" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypePayPalHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypePayPalHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypePayPalHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypePayPalHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- PAYPAL END -->
                                            <!-- SOFORTUEBERWEISUNG BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSofortueberweisung"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_USERID')); ?>"
                                                        for="bfElementTypeSofortueberweisungUserId">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_USERID'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSofortueberweisungUserId" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_PROJECTID')); ?>"
                                                        for="bfElementTypeSofortueberweisungProjectId">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_PROJECTID'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSofortueberweisungProjectId" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_PROJECTPASSWORD')); ?>"
                                                        for="bfElementTypeSofortueberweisungProjectPassword">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_PROJECT_PASSWORD'); ?>
                                                    </label>
                                                    <input type="password" value=""
                                                        id="bfElementTypeSofortueberweisungProjectPassword" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_REASON1')); ?>"
                                                        for="bfElementTypeSofortueberweisungReason1">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_REASON1'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSofortueberweisungReason1" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_REASON2')); ?>"
                                                        for="bfElementTypeSofortueberweisungReason2">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_REASON2'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSofortueberweisungReason2" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_AMOUNT')); ?>"
                                                        for="bfElementTypeSofortueberweisungAmount">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_AMOUNT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSofortueberweisungAmount" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_THANKYOUPAGE')); ?>"
                                                        for="bfElementTypeSofortueberweisungThankYouPage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_THANKYOU_PAGE'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementTypeSofortueberweisungThankYouPage" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_LANGUAGEID')); ?>"
                                                        for="bfElementTypeSofortueberweisungLanguageId">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_LANGUAGE_ID'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementTypeSofortueberweisungLanguageId" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_CURRENCY')); ?>"
                                                        for="bfElementTypeSofortueberweisungCurrencyId">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_CURRENCY_ID'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementTypeSofortueberweisungCurrencyId" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_SENDNOTAFPAY')); ?>"
                                                        for="bfElementTypeSofortueberweisungSendNotificationAfterPayment">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_NOTIFICATION_AFTER_PAYMENT'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementTypeSofortueberweisungSendNotificationAfterPayment" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_MAILBACK')); ?>"
                                                        for="bfElementTypeSofortueberweisungMailback">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_MAILBACK'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementTypeSofortueberweisungMailback" />
                                                </div>
                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSofortueberweisungHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeSofortueberweisungHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSofortueberweisungHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeSofortueberweisungHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- SOFORTUEBERWEISUNG END -->
                                            <!-- CAPTCHA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCaptcha" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCaptchaHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCaptchaHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCaptchaHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCaptchaHintTrans"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CAPTCHA_WIDTH')); ?>"
                                                        for="bfElementTypeCaptchaWidth">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_CAPTCHA_WIDTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCaptchaWidth" />
                                                </div>
                                            </div>
                                            <!-- CAPTCHA END -->
                                            <!-- RECAPTCHA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeReCaptcha" style="display:none">

                                                <div class="bfPropertyWrap">
                                                    <label for="bfElementTypeReCaptchaNew" class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_RECAPTCHA_NEW_CAPTCHA_HINT')); ?>">reCaptcha</label>

                                                    <label>
                                                        <input type="radio" name="bfElementTypeReCaptchaNew" value=""
                                                            id="bfElementTypeReCaptchaNew" checked="checked" />
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_RECAPTCHA_NEW_CAPTCHA'); ?>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="bfElementTypeReCaptchaNew" value=""
                                                            id="bfElementTypeReCaptchaInvisible" />
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_RECAPTCHA_INVISIBLE_CAPTCHA'); ?>
                                                    </label>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_RECAPTCHA_PUBKEY')); ?>"
                                                        for=bfElementTypeReCaptchaPubkey><?php echo BFText::_('COM_BREEZINGFORMSNG_PUBLIC_KEY'); ?></label>
                                                    <input type="text" value="" id="bfElementTypeReCaptchaPubkey" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_RECAPTCHA_PRIVKEY')); ?>"
                                                        for=bfElementTypeReCaptchaPrivkey><?php echo BFText::_('COM_BREEZINGFORMSNG_PRIVATE_KEY'); ?></label>
                                                    <input type="text" value="" id="bfElementTypeReCaptchaPrivkey" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_RECAPTCHA_THEME')); ?>"
                                                        for=bfElementTypeReCaptchaTheme><?php echo BFText::_('COM_BREEZINGFORMSNG_Theme'); ?></label>
                                                    <input type="text" value="red" id="bfElementTypeReCaptchaTheme" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_RECAPTCHA_SIZE')); ?>"
                                                        for=bfElementTypeReCaptchaSize><?php echo BFText::_('COM_BREEZINGFORMSNG_RECAPTCHA_SIZE'); ?></label>
                                                    <input type="text" value="normal" id="bfElementTypeReCaptchaSize" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeReCaptchaHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeReCaptchaHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeReCaptchaHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeReCaptchaHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- RECAPTCHA END -->
                                            <!-- CALENDAR Responsive BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCalendarResponsive"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_FORMAT')); ?>"
                                                        for="bfElementTypeCalendarResponsiveFormat">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_FORMAT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarResponsiveFormat" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_FORMAT')); ?>"
                                                        for="bfElementTypeCalendarResponsiveFormatTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_FORMAT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementTypeCalendarResponsiveFormatTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_VALUE')); ?>"
                                                        for="bfElementTypeCalendarResponsiveValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarResponsiveValue" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_VALUE')); ?>"
                                                        for="bfElementTypeCalendarResponsiveValueTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementTypeCalendarResponsiveValueTrans" />
                                                </div>
                                                <?php
                                                // Icon for Responsive Calendar
                                                // This code is only for NEW THEME

                                                if ($isAzureBootstrapTheme) {
                                                    ?>

                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ICON')); ?>"
                                                            for="bfElementTypeCalendarResponsiveIcon">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_ICON'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementTypeCalendarResponsiveIcon" />
                                                    </div>

                                                <?php } ?>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_SIZE')); ?>"
                                                        for="bfElementTypeCalendarResponsiveSize">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_SIZE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarResponsiveSize" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCalendarResponsiveHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCalendarResponsiveHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCalendarResponsiveHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCalendarResponsiveHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- CALENDAR RESPONSIVE END -->
                                            <!-- CALENDAR BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCalendar" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_FORMAT')); ?>"
                                                        for="bfElementTypeCalendarFormat">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_FORMAT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarFormat" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_FORMAT')); ?>"
                                                        for="bfElementTypeCalendarFormatTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_FORMAT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarFormatTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_VALUE')); ?>"
                                                        for="bfElementTypeCalendarValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarValue" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_VALUE')); ?>"
                                                        for="bfElementTypeCalendarValueTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarValueTrans" />
                                                </div>

                                                <?php
                                                // Icon for Calendar
                                                // This code is only for NEW THEME

                                                if ($isAzureBootstrapTheme) {
                                                    ?>

                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ICON')); ?>"
                                                            for="bfElementTypeCalendarIcon">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_ICON'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementTypeCalendarIcon" />
                                                    </div>

                                                <?php } ?>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_SIZE')); ?>"
                                                        for="bfElementTypeCalendarSize">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_SIZE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarSize" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCalendarHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCalendarHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCalendarHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCalendarHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- CALENDAR END -->
                                            <!-- SIGNATURE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSignature" style="display:none">

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSignatureHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeSignatureHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSignatureHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeSignatureHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- SIGNATURE END -->
                                            <!-- CHECKBOXGROUP BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCheckboxGroup"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CHECKBOX_GROUP')); ?>"
                                                        for="bfElementTypeCheckboxGroupGroups">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_GROUP'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxGroupGroups"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CHECKBOX_GROUP')); ?>"
                                                        for="bfElementTypeCheckboxGroupGroupsTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_GROUP'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxGroupGroupsTrans"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CHECKBOX_GROUP_READONLY')); ?>"
                                                        for="bfElementTypeCheckboxGroupReadonly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeCheckboxGroupReadonly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CHECKBOX_GROUP_WRAP')); ?>"
                                                        for="bfElementTypeCheckboxGroupWrap">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_WRAP'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeCheckboxGroupWrap" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCheckboxGroupHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxGroupHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCheckboxGroupHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxGroupHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- CHECKBOXGROUP END -->
                                            <!-- CHECKBOX BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCheckbox" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_CHECKBOX_VALUE')); ?>"
                                                        for="bfElementTypeCheckboxValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxValue"></textarea>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_CHECKBOX_CHECKED')); ?>"
                                                        for="bfElementTypeCheckboxChecked">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_CHECKED'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeCheckboxChecked" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_CHECKBOX_READONLY')); ?>"
                                                        for="bfElementTypeCheckboxReadonly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeCheckboxReadonly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCheckboxHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCheckboxHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- CHECKBOX END -->
                                            <!-- SELECT BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSelect" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SELECTLIST')); ?>"
                                                        for="bfElementTypeSelectList">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_LIST'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeSelectList"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SELECTLIST')); ?>"
                                                        for="bfElementTypeSelectListTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_LIST'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeSelectListTrans"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SELECTLIST_MULTIPLE')); ?>"
                                                        for="bfElementTypeSelectMultiple">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_MULTIPLE'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeSelectMultiple" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SELECTLIST_WIDTH')); ?>"
                                                        for="bfElementTypeSelectListWidth">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_WIDTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSelectListWidth" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SELECTLIST_HEIGHT')); ?>"
                                                        for="bfElementTypeSelectListHeight">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HEIGHT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSelectListHeight" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SELECTLIST_READONLY')); ?>"
                                                        for="bfElementTypeSelectReadonly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeSelectReadonly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSelectHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeSelectHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSelectHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeSelectHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- SELECT END -->
                                            <!-- FILE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeFile" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_READONLY')); ?>"
                                                        for="bfElementTypeFileReadonly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeFileReadonly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeFileHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeFileHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeFileHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeFileHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- FILE END -->
                                        </fieldset>
                                        <fieldset id="bfValidationScript" style="display:none">
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALIDATION'); ?>
                                            </legend>
                                            <span id="bfElementValidationRequiredSet" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_VALIDATION_REQUIRED')); ?>"
                                                        for="bfElementValidationRequired">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALIDATION_REQUIRED'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementValidationRequired" />
                                                </div>
                                            </span>

                                            <div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_VALIDATION_TYPE')); ?>"
                                                        for="bfElementValidation">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_VALIDATION_LABEL'); ?>
                                                    </label>
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_TYPE') ?>:
                                                    <input
                                                        onclick="JQuery('#bfValidationScriptFlags').css('display', 'none');
                                                                            JQuery('#bfValidationScriptLibrary').css('display', 'none');JQuery('#bfValidationScriptCustom').css('display', 'none');"
                                                        type="radio" name="validationType" id="bfValidationTypeNone"
                                                        class="bfValidationType" value="0" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_NONE') ?>
                                                    <input
                                                        onclick="JQuery('#bfValidationScriptFlags').css('display', '');
                                                                            JQuery('#bfValidationScriptLibrary').css('display', '');JQuery('#bfValidationScriptCustom').css('display', 'none');"
                                                        type="radio" name="validationType" id="bfValidationTypeLibrary"
                                                        class="bfValidationType" value="1" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_LIBRARY') ?>
                                                    <input
                                                        onclick="JQuery('#bfValidationScriptFlags').css('display', '');JQuery('#bfValidationScriptLibrary').css('display', 'none');
                                                                            JQuery('#bfValidationScriptCustom').css('display', '');"
                                                        type="radio" name="validationType" id="bfValidationTypeCustom"
                                                        class="bfValidationType" value="2" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_CUSTOM') ?>
                                                </div>

                                                <div id="bfValidationScriptFlags" style="display:none">
                                                    <hr />
                                                    <div class="bfPropertyWrap">
                                                        <span class="hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_VALIDATION_ERROR_MESSAGE')); ?>">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_ERROR_MESSAGE') ?>
                                                            :
                                                        </span> <input type="text" style="width:100%" maxlength="255"
                                                            class="bfValidationMessage" id="bfValidationMessage"
                                                            name="bfValidationMessage" value="" class="inputbox" />
                                                    </div>

                                                    <div class="bfPropertyWrap bfTrans">
                                                        <span class="hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_VALIDATION_ERROR_MESSAGE')); ?>">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_ERROR_MESSAGE') ?>
                                                            <em>(
                                                                <?php echo $active_language_code ?>)
                                                            </em>:
                                                        </span>
                                                        <input type="text" style="width:100%" maxlength="255"
                                                            class="bfValidationMessage" id="bfValidationMessageTrans"
                                                            name="bfValidationMessage" value="" class="inputbox" />
                                                    </div>
                                                </div>

                                                <div id="bfValidationScriptLibrary" style="display:none">
                                                    <hr />
                                                    <div class="bfPropertyWrap">
                                                        <span class="hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_VALIDATION_SCRIPTLIBRARY')); ?>">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_SCRIPT') ?>
                                                            :
                                                        </span><br /> <select id="bfValidationScriptSelection"></select>
                                                    </div>
                                                    <br />
                                                    <div id="bfValidationScriptSelectionDescription" style="white-space: pre-line;"></div>
                                                </div>

                                                <div id="bfValidationScriptCustom" style="display:none">
                                                    <hr />
                                                    <div class="bfPropertyWrap">
                                                        <div class="hasTooltip btn btn-secondary"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_VALIDATION_CODEFRAMEWORK')); ?>"
                                                            style="cursor: pointer;" onclick="createValidationCode()">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_CREATE_CODE_FRAMEWORK') ?>
                                                        </div>

                                                        <?php
                                                        echo self::renderCodeMirror('bfValidationCode');
                                                        ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <br />
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
                                    <div class="bfAdvanced" id="bfPageAdvanced" style="display:none">
                                    </div>
                                    <div class="bfAdvanced" id="bfFormAdvanced" style="display:none">
                                        <br />
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_ADVANCED_FORM_OPTIONS'); ?>
                                            </legend>
                                            <?php if ($formId != 0) { ?>
                                                <a href="index.php?option=com_breezingformsng&task=editform&act=manageforms&form=<?php echo $formId ?>&pkg=QuickModeForms"
                                                    title="<?php echo BFText::_('COM_BREEZINGFORMSNG_MORE_OPTIONS'); ?>">
                                                    <?php echo htmlentities(BFText::_('COM_BREEZINGFORMSNG_MORE_OPTIONS'), ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                            <?php } ?>
                                        </fieldset>
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_ADVANCED_FORM_THEMES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_ENGINE')); ?>"
                                                    for="bfThemeBootstrapThemeBreezingForms">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_CHOOSE_THEME_ENGINE'); ?>
                                                </label>
                                                <input
                                                    onclick="JQuery('#bfThemeBootstrapDiv').css('display', 'none');JQuery('#bfThemeBreezingFormsDiv').css('display', 'block');" type="radio"
                                                    name="bfThemeBootstrapThemeEngine" value=""
                                                    id="bfThemeBootstrapThemeBreezingForms" /> BreezingForms
                                                <input
                                                    onclick="JQuery('#bfThemeBootstrapDiv').css('display', 'block'); JQuery('#bfThemeBreezingFormsDiv').css('display', 'none');" 
                                                    type="radio"
                                                    name="bfThemeBootstrapThemeEngine" value=""
                                                    id="bfThemeBootstrapThemeBootstrap" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_THEME_ENGINE_BOOTSTRAP'); ?>
                                            </div>
                                            <div id="bfThemeBreezingFormsDiv" style="display:none;">
                                                <br />
                                                <legend>
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_ADVANCED_THEME_BREEZINGFORMS_ENGINE'); ?>
                                                </legend>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_NATIVE')); ?>"
                                                        for="bfTheme">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_THEME'); ?>
                                                    </label>
                                                    <select id="bfTheme">
                                                        <?php
                                                        $tCount = count($themes);
                                                        for ($i = 0; $i < $tCount; $i++) {
                                                            echo '<option value="' . $themes[$i] . '">' . $themes[$i] . '</option>' . "\n";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div id="bfThemeBootstrapDiv" style="display:none;">
                                                <legend>
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_ADVANCED_THEME_BOOTSTRAP_ENGINE'); ?>
                                                </legend>
                                                <br />
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP')); ?>"
                                                        for="bfThemeBootstrap">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_THEME_BOOTSTRAP'); ?>
                                                    </label>
                                                    <?php
                                                    $dbObject = Zend_Json::decode($dataObjectString);
                                                    $useBs3 = false;
                                                    if (isset($dbObject['properties']['themebootstrapUse3']) && $dbObject['properties']['themebootstrapUse3']) {
                                                        $useBs3 = true;
                                                    }
                                                    ?>
                                                    <select id="bfThemeBootstrap" <?php echo $useBs3 ? ' style="display: none;"' : ''; ?>>
                                                        <option value="">Default</option>
                                                        >
                                                        <?php
                                                        $tCount = count($themesbootstrap);
                                                        for ($i = 0; $i < $tCount; $i++) {
                                                            echo '<option value="' . $themesbootstrap[$i] . '">' . $themesbootstrap[$i] . '</option>' . "\n";
                                                        }
                                                        ?>
                                                    </select>
                                                    <select id="bfThemeBootstrap3" <?php echo !$useBs3 ? ' style="display: none;"' : ''; ?>>
                                                        <option value="">Default</option>
                                                        >
                                                        <?php
                                                        $tCount = count($themesbootstrap3);
                                                        for ($i = 0; $i < $tCount; $i++) {
                                                            echo '<option value="' . $themesbootstrap3[$i] . '">' . $themesbootstrap3[$i] . '</option>' . "\n";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
  
                                                <div class="bfPropertyWrap" style="display:none;">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_USE_BOOTSTRAP3_TIP')); ?>"
                                                        for="bfThemeBootstrapUse3Yes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_THEME_USE_BOOTSTRAP3'); ?>
                                                    </label>

                                                    <input
                                                        onclick="JQuery('#bfThemeBootstrap3').css('display', 'block');
                                                                                        JQuery('#bfThemeBootstrap').css('display', 'none');"
                                                        type="radio" name="bfThemeBootstrapUse3" value=""
                                                        id="bfThemeBootstrapUse3Yes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_YES'); ?>
                                                    <input checked="checked"
                                                        onclick="JQuery('#bfThemeBootstrap').css('display', 'block');
                                                                                        JQuery('#bfThemeBootstrap3').css('display', 'none');"
                                                        type="radio" name="bfThemeBootstrapUse3" value=""
                                                        id="bfThemeBootstrapUse3No" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_NO'); ?>
                                                </div>

                                                <div class="bfPropertyWrap" style="display:none;">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP3_BUILTIN_TIP')); ?>"
                                                        for="bfThemeBootstrap3BuiltInYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP3_BUILTIN'); ?>
                                                    </label>

                                                    <input checked="checked" type="radio" name="bfThemeBootstrap3BuiltIn"
                                                        value="" id="bfThemeBootstrap3BuiltInYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_YES'); ?>
                                                    <input type="radio" name="bfThemeBootstrap3BuiltIn" value=""
                                                        id="bfThemeBootstrap3BuiltInNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_NO'); ?>
                                                </div>

                                                <div class="bfPropertyWrap" style="display:none;">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP3_CLASSPFX_TIP')); ?>"
                                                        for="bfThemeBootstrap3Classpfx">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP3_CLASSPFX'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfThemeBootstrap3Classpfx" />
                                                </div>


                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP_MODE_TIP')); ?>"
                                                        for="bfThemeBootstrapModeYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP_MODE'); ?>
                                                    </label>

                                                    <input type="radio" name="bfThemeBootstrapMode" value=""
                                                        id="bfThemeBootstrapModeYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_YES'); ?>
                                                    <input checked="checked" type="radio" name="bfThemeBootstrapMode" value=""
                                                        id="bfThemeBootstrapModeNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_NO'); ?>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP_LABEL_TOP')); ?>"
                                                        for="bfThemeBootstrapLabelTopYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_THEME_BOOTSTRAP_LABELTOP'); ?>
                                                    </label>

                                                    <input type="radio" name="bfThemeBootstrapLabelTop" value=""
                                                        id="bfThemeBootstrapLabelTopYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_YES'); ?>
                                                    <input checked="checked" type="radio" name="bfThemeBootstrapLabelTop"
                                                        value="" id="bfThemeBootstrapLabelTopNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_NO'); ?>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP_HERO_UNIT')); ?>"
                                                        for="bfThemeBootstrapUseHeroUnitYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_THEME_BOOTSTRAP_USE_HERO_UNIT'); ?>
                                                    </label>

                                                    <input type="radio" name="bfThemeBootstrapUseHeroUnit" value=""
                                                        id="bfThemeBootstrapUseHeroUnitYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_YES'); ?>
                                                    <input checked="checked" type="radio" name="bfThemeBootstrapUseHeroUnit"
                                                        value="" id="bfThemeBootstrapUseHeroUnitNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_NO'); ?>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP_WELL')); ?>"
                                                        for="bfThemeBootstrapUseWellYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_THEME_BOOTSTRAP_USE_WELL'); ?>
                                                    </label>

                                                    <input type="radio" name="bfThemeBootstrapUseWell" value=""
                                                        id="bfThemeBootstrapUseWellYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_YES'); ?>
                                                    <input checked="checked" type="radio" name="bfThemeBootstrapUseWell"
                                                        value="" id="bfThemeBootstrapUseWellNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_NO'); ?>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP_PROGRESS')); ?>"
                                                        for="bfThemeBootstrapUseProgressYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_THEME_BOOTSTRAP_USE_PROGRESS'); ?>
                                                    </label>

                                                    <input type="radio" name="bfThemeBootstrapUseProgress" value=""
                                                        id="bfThemeBootstrapUseProgressYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_YES'); ?>
                                                    <input checked="checked" type="radio" name="bfThemeBootstrapUseProgress"
                                                        value="" id="bfThemeBootstrapUseProgressNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_NO'); ?>
                                                </div>

                                                <?php
                                                $dbObject = Zend_Json::decode($dataObjectString);
                                                if (isset($dbObject['properties']['themebootstrap'])) {
                                                    $themeboostrapfolder = $dbObject['properties']['themebootstrap'];
                                                    $folder = 'themes-bootstrap';
                                                    if (isset($dbObject['properties']['themebootstrapUse3']) && $dbObject['properties']['themebootstrapUse3']) {
                                                        $folder = 'themes-bootstrap3';
                                                    }
                                                    $themesbootstrap_path = JPATH_SITE . '/media/breezingforms/' . $folder . '/' . $themeboostrapfolder . '/';
                                                    if (is_dir($themesbootstrap_path) && file_exists($themesbootstrap_path . 'vars.txt')) {
                                                        $varscontent = htmlentities(BFFile::read($themesbootstrap_path . 'vars.txt'), ENT_QUOTES, 'UTF-8');
                                                        if ($varscontent) {
                                                            echo '<br/>
                                                                <div class="bfPropertyWrap">
                                                                <label class="bfPropertyLabel hasTooltip" title="' . bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_THEME_BOOTSTRAP_PROGRESS')) . '" for="bfThemeBootstrapVars">' . BFText::_('COM_BREEZINGFORMSNG_THEME_BOOTSTRAP_VARS') . '</label>
                                                                <textarea id="bfThemeBootstrapVars">' . $varscontent . '</textarea>
                                                                </div>
                                                                <input type="hidden" name="bfThemeBootstrapBefore" id="bfThemeBootstrapBefore" value=""/>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </div>
 
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_ADVANCED_FORM_OTHER'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_MOBILE_FORMS')); ?>"
                                                    for="bfElementAdvancedMobileEnabled">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_MOBILE_ENABLED'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfElementAdvancedMobileEnabled" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_MOBILE_FORMS_FORCE')); ?>"
                                                    for="bfElementAdvancedForceMobile">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORCE_MOBILE'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfElementAdvancedForceMobile" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_MOBILE_FORMS_URL')); ?>"
                                                    for="bfElementAdvancedForceMobileUrl">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORCE_MOBILE_URL'); ?>
                                                </label>
                                                <input type="text" value="" id="bfElementAdvancedForceMobileUrl" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_HINT_JOOMLA')); ?>"
                                                    for="bfElementAdvancedJoomlaHint">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_JOOMLA_HINT'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfElementAdvancedJoomlaHint" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_JQUERY_DISABLE')); ?>"
                                                    for="bfElementAdvancedDisableJQuery">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_DISABLE_JQUERY'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfElementAdvancedDisableJQuery" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ERROR_ALERTS')); ?>"
                                                    for="bfElementAdvancedUseErrorAlerts">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_USE_ERROR_ALERTS'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfElementAdvancedUseErrorAlerts" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ERROR_DEFAULT')); ?>"
                                                    for="bfElementAdvancedUseDefaultErrors">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_IF_NOT_USE_ERROR_ALERTS'); ?>
                                                </label>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_IF_USE_DEFAULT_ERRORS'); ?>
                                                <input type="checkbox" value="" id="bfElementAdvancedUseDefaultErrors" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_IF_USE_BALLOON_ERRORS'); ?>
                                                <input type="checkbox" value="" id="bfElementAdvancedUseBalloonErrors" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <div id="bfFadingEffectToggle">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FADE_IN')); ?>"
                                                        for="bfElementAdvancedFadeIn">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FADE_IN'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedFadeIn" />
                                                </div>
                                            </div>
                                            <div id="bfRollOverToggle">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ROLLOVER')); ?>"
                                                        for="bfElementAdvancedRollover">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ROLLOVER'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedRollover" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ROLLOVER_COLOR')); ?>"
                                                        for="bfElementAdvancedRolloverColor">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ROLLOVER_COLOR'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementAdvancedRolloverColor" />
                                                </div>

                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_VISIBILITY_RULES')); ?>"
                                                    for="bfElementAdvancedToggleFields">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_FORM_TOGGLEFIELDS'); ?>
                                                </label>
                                                <textarea id="bfElementAdvancedToggleFields"></textarea>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div class="bfAdvanced" id="bfSectionAdvanced" style="display:none">
                                        <div class="bfPropertyWrap">
                                            <label class="bfPropertyLabel hasTooltip"
                                                title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_TURNOFF')); ?>"
                                                for="bfSectionAdvancedTurnOff">
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_TURN_OFF_INITIALLY'); ?>
                                            </label>
                                            <input type="checkbox" value="" id="bfSectionAdvancedTurnOff" />
                                        </div>
                                    </div>
                                    <div class="bfAdvanced" id="bfElementAdvanced" style="display:none">
                                        <br />
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_ADVANCED_ELEMENT_OPTIONS'); ?>
                                            </legend>
                                            <div></div>
                                            <!-- HIDDEN BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeHiddenAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementHiddenAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementHiddenAdvancedOrderNumber" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementHiddenAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_LOGGING'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementHiddenAdvancedLogging" />
                                                </div>
                                            </div>
                                            <!-- HIDDEN END -->

                                            <!-- NUMBER INPUT BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeNumberInputAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementNumberInputAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementNumberInputAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementNumberInputAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementNumberInputAdvancedLogging" />
                                                </div>
                                            <!-- Begin option Range -->
                                                <div class="bfPropertyWrap"> 
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_RANGE')); ?>"
                                                        for="bfElementNumberInputAdvancedRange">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_RANGE'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementNumberInputAdvancedRange" />
                                                </div>
                                            <!-- End option Range -->
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_NUMBER_INPUT_STEP_TOOLTIP')); ?>"
                                                        for="bfElementNumberInputAdvancedStep">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_NUMBER_INPUT_STEP'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementNumberInputAdvancedStep" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_NUMBER_INPUT_MIN_TOOLTIP')); ?>"
                                                        for="bfElementNumberInputAdvancedMin">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_NUMBER_INPUT_MIN'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementNumberInputAdvancedMin" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_NUMBER_INPUT_MAX_TOOLTIP')); ?>"
                                                        for="bfElementNumberInputAdvancedMax">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_NUMBER_INPUT_MAX'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementNumberInputAdvancedMax" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementNumberInputOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementNumberInputOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- NUMBER INPUT END -->
                                            <!--  SUMMARIZE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSummarizeAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SUMMARIZE_CALC')); ?>"
                                                        for="bfElementAdvancedSummarizeCalc">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENT_CALC'); ?>
                                                    </label>
                                                    <textarea id="bfElementAdvancedSummarizeCalc"></textarea>
                                                </div>
                                            </div>
                                            <!--  SUMMARIZE END -->

                                            <!-- TEXTFIELD BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeTextAdvanced" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_TEXTFIELD_PASSWORD')); ?>"
                                                        for="bfElementAdvancedPassword">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_PASSWORD'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedPassword" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_TEXTFIELD_READONLY')); ?>"
                                                        for="bfElementAdvancedReadOnly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedReadOnly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_TEXTFIELD_MAILBACK')); ?>"
                                                        for="bfElementAdvancedMailback">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_MAILBACK'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedMailback" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_TEXTFIELD_MAILBACK_AS_SENDER')); ?>"
                                                        for="bfElementAdvancedMailbackAsSender">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_MAILBACK_AS_SENDER'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedMailbackAsSender" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_TEXTFIELD_MAILBACK_FILE')); ?>"
                                                        for="bfElementAdvancedMailbackfile">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_MAILBACKFILE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementAdvancedMailbackfile" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- TEXTFIELD END -->
                                            <!-- TEXTAREA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeTextareaAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementTextareaAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementTextareaAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementTextareaAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementTextareaAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementTextareaAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTextareaAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- TEXTAREA END -->

                                            <!-- RADIOGROUP BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeRadioGroupAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementRadioGroupAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementRadioGroupAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementRadioGroupAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementRadioGroupAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementRadioGroupAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementRadioGroupAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- RADIOGROUP END -->
                                            <!-- SUBMITBUTTON BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSubmitButtonAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementSubmitButtonAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSubmitButtonAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SUBMIT_BUTTON_SOURCE')); ?>"
                                                        for="bfElementSubmitButtonAdvancedSrc">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_SOURCE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementSubmitButtonAdvancedSrc" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SUBMIT_BUTTON_SOURCE')); ?>"
                                                        for="bfElementSubmitButtonAdvancedSrcTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_SOURCE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementSubmitButtonAdvancedSrcTrans" />
                                                </div>
                                            </div>
                                            <!-- SUBMITBUTTON END -->


                                            <!-- STRIPE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeStripeAdvanced"
                                                style="display:none">


                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_IMAGE')); ?>"
                                                        for="bfElementStripeAdvancedImage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_IMAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementStripeAdvancedImage" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_IMAGE')); ?>"
                                                        for="bfElementStripeAdvancedImageTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_IMAGE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementStripeAdvancedImageTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementStripeAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementStripeAdvancedHideLabel" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_FILE')); ?>"
                                                        for="bfElementStripeAdvancedDownloadableFile">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_DOWNLOADABLE_FILE'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementStripeAdvancedDownloadableFile" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_FILEPATH')); ?>"
                                                        for="bfElementStripeAdvancedFilepath">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FILEPATH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementStripeAdvancedFilepath" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_TRIES')); ?>"
                                                        for="bfElementStripeAdvancedDownloadTries">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_DOWNLOAD_TRIES'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementStripeAdvancedDownloadTries" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltiptext(BFText::_('COM_BREEZINGFORMSNG_QM_STRIPE_EMAIL')); ?>"
                                                        for="bfElementStripeAdvancedEmailField">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_STRIPE_EMAIL'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementStripeAdvancedEmailField" />
                                                </div>
                                            </div>
                                            <!-- STRIPE END -->



                                            <!-- PAYPAL BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypePayPalAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_IPN')); ?>"
                                                        for="bfElementPayPalAdvancedUseIpn">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_USE_IPN'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementPayPalAdvancedUseIpn" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_USE_IPN_DESCRIPTION'); ?>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementPayPalAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementPayPalAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_IMAGE')); ?>"
                                                        for="bfElementPayPalAdvancedImage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_IMAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedImage" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_IMAGE')); ?>"
                                                        for="bfElementPayPalAdvancedImageTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_IMAGE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedImageTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_TESTACCOUNT')); ?>"
                                                        for="bfElementPayPalAdvancedTestaccount">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_TESTACCOUNT'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementPayPalAdvancedTestaccount" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_TESTBUSINESS')); ?>"
                                                        for="bfElementPayPalAdvancedTestBusiness">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_TESTBUSINESS'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedTestBusiness" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_TESTTOKEN')); ?>"
                                                        for="bfElementPayPalAdvancedTestToken">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_TESTTOKEN'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedTestToken" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_FILE')); ?>"
                                                        for="bfElementPayPalAdvancedDownloadableFile">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_DOWNLOADABLE_FILE'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementPayPalAdvancedDownloadableFile" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_FILEPATH')); ?>"
                                                        for="bfElementPayPalAdvancedFilepath">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FILEPATH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedFilepath" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAYPAL_TRIES')); ?>"
                                                        for="bfElementPayPalAdvancedDownloadTries">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_DOWNLOAD_TRIES'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedDownloadTries" />
                                                </div>
                                            </div>
                                            <!-- PAYPAL END -->
                                            <!-- SOFORTUEBERWEISUNG BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSofortueberweisungAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSofortueberweisungAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_IMAGE')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedImage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_IMAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementSofortueberweisungAdvancedImage" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_IMAGE')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedImageTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_IMAGE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementSofortueberweisungAdvancedImageTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_FILE')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedDownloadableFile">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_DOWNLOADABLE_FILE'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSofortueberweisungAdvancedDownloadableFile" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_FILEPATH')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedFilepath">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FILEPATH'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementSofortueberweisungAdvancedFilepath" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SOFORT_TRIES')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedDownloadTries">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_DOWNLOAD_TRIES'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementSofortueberweisungAdvancedDownloadTries" />
                                                </div>
                                            </div>
                                            <!-- SOFORTUEBERWEISUNG END -->
                                            <!-- CAPTCHA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCaptchaAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementCaptchaAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCaptchaAdvancedHideLabel" />
                                                </div>
                                            </div>
                                            <!-- CAPTCHA END -->
                                            <!-- RECAPTCHA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeReCaptchaAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementReCaptchaAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementReCaptchaAdvancedHideLabel" />
                                                </div>
                                            </div>
                                            <!-- RECAPTCHA END -->
                                            <!-- CALENDAR RESPONSIVE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCalendarResponsiveAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementCalendarResponsiveAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCalendarResponsiveAdvancedHideLabel" />
                                                </div>
                                            </div>
                                            <!-- CALENDAR RESPONSIVE END -->
                                            <!-- CALENDAR BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCalendarAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementCalendarAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCalendarAdvancedHideLabel" />
                                                </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_SHOW_TIME_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedShowTime">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_SHOW_TIME'); ?>
                                                        </label>
                                                        <input checked="checked" type="checkbox" value=""
                                                            id="bfElementCalendarAdvancedShowTime" />
                                                    </div>
                                                    <div class="bfPropertyWrap" style="display:none;">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_FIRST_DAY_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedFirstDay">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_FIRST_DAY'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementCalendarAdvancedFirstDay" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_24H_TIME_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedTimeFormat">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_24H_TIME'); ?>
                                                        </label>
                                                        <input checked="checked" type="checkbox" value=""
                                                            id="bfElementCalendarAdvancedTimeFormat" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_SINGLE_HEADER_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedSingleHeader">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_SINGLE_HEADER'); ?>
                                                        </label>
                                                        <input checked="checked" type="checkbox" value=""
                                                            id="bfElementCalendarAdvancedSingleHeader" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_TODAY_BTN_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedTodayButton">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_TODAY_BTN'); ?>
                                                        </label>
                                                        <input checked="checked" type="checkbox" value=""
                                                            id="bfElementCalendarAdvancedTodayButton" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_WEEK_NMBR_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedWeekNumbers">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_WEEK_NMBR'); ?>
                                                        </label>
                                                        <input checked="checked" type="checkbox" value=""
                                                            id="bfElementCalendarAdvancedWeekNumbers" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_MIN_YEAR_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedMinYear">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_MIN_YEAR'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementCalendarAdvancedMinYear" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_MAX_YEAR_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedShowTime">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_QM_CALENDAR_MAX_YEAR'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementCalendarAdvancedMaxYear" />
                                                    </div>
                                            </div>
                                            <!-- CALENDAR END -->
                                            <!-- SIGNATURE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSignatureAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementSignatureAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSignatureAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementSignatureAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSignatureAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementSignatureAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementSignatureAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- SIGNATURE END -->
                                            <!-- CHECKBOXGROUP BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCheckboxGroupAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementCheckboxGroupAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCheckboxGroupAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementCheckboxGroupAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCheckboxGroupAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementCheckboxGroupAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementCheckboxGroupAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- CHECKBOXGROUP END -->
                                            <!-- CHECKBOX BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCheckboxAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_MAILBACK_ACCEPT')); ?>"
                                                        for="bfElementCheckboxAdvancedMailbackAccept">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_MAILBACK_ACCEPT'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCheckboxAdvancedMailbackAccept" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_MAILBACK_CONNECTWITH')); ?>"
                                                        for="bfElementCheckboxAdvancedMailbackConnectWith">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_MAILBACK_CONNECT_WITH'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementCheckboxAdvancedMailbackConnectWith" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementCheckboxAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCheckboxAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementCheckboxAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCheckboxAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementCheckboxAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementCheckboxAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- CHECKBOX END -->
                                            <!-- CHECKBOXGROUP BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSelectAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementSelectAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSelectAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SELECT_MAILBACK')); ?>"
                                                        for="bfElementSelectAdvancedMailback">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_MAILBACK'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSelectAdvancedMailback" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementSelectAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSelectAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementSelectAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementSelectAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- CHECKBOXGROUP END -->
                                            <!-- FILE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeFileAdvanced" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_HTML5')); ?>"
                                                        for="bfElementFileAdvancedHtml5Uploader">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HTML5_UPLOADER'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementFileAdvancedHtml5Uploader" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_FLASH')); ?>"
                                                        for="bfElementFileAdvancedFlashUploader">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FLASH_UPLOADER'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementFileAdvancedFlashUploader" />
                                                    <br />
                                                    <br />
                                                    <i>(
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FLASH_UPLOADER_HINT'); ?>
                                                        )
                                                    </i>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_MULTI')); ?>"
                                                        for="bfElementFileAdvancedFlashUploaderMulti">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FLASH_UPLOADER_MULTI'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementFileAdvancedFlashUploaderMulti" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_BYTES')); ?>"
                                                        for="bfElementFileAdvancedFlashUploaderBytes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FLASH_UPLOADER_BYTES'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedFlashUploaderBytes" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_WIDTH')); ?>"
                                                        for="bfElementFileAdvancedFlashUploaderWidth">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FLASH_UPLOADER_WIDTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedFlashUploaderWidth" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_HEIGHT')); ?>"
                                                        for="bfElementFileAdvancedFlashUploaderHeight">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FLASH_UPLOADER_HEIGHT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedFlashUploaderHeight" />
                                                </div>
                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_TRANSPARENT')); ?>"
                                                        for="bfElementFileAdvancedFlashUploaderTransparent">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FLASH_UPLOADER_TRANSPARENT'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementFileAdvancedFlashUploaderTransparent" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_UPLOAD_DIRECTORY')); ?>"
                                                        for="bfElementFileAdvancedUploadDirectory">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_UPLOAD_DIRECTORY'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedUploadDirectory" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_RESIZE_WIDTH')); ?>"
                                                        for="bfElementFileAdvancedResizeTargetWidth">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_RESIZE_TARGET_WIDTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedResizeTargetWidth"
                                                        value="0" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_RESIZE_HEIGHT')); ?>"
                                                        for="bfElementFileAdvancedResizeTargetHeight">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_RESIZE_TARGET_HEIGHT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedResizeTargetHeight"
                                                        value="0" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_RESIZE_TYPE')); ?>"
                                                        for="bfElementFileAdvancedResizeType">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_RESIZE_TYPE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedResizeType" value="" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_RESIZE_BGCOLOR')); ?>"
                                                        for="bfElementFileAdvancedResizeBgcolor">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_RESIZE_BGCOLOR'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedResizeBgcolor"
                                                        value="#ffffff" />
                                                    <br />
                                                    <br />
                                                    <i>(
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_RESIZE_HINT'); ?>)
                                                    </i>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_TIMESTAMP')); ?>"
                                                        for="bfElementFileAdvancedTimestamp">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_TIMESTAMP'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedTimestamp" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_EXTENSIONS')); ?>"
                                                        for="bfElementFileAdvancedAllowedFileExtensions">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ALLOWED_FILE_EXTENSIONS'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementFileAdvancedAllowedFileExtensions" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_ATTACHUSERMAIL')); ?>"
                                                        for="bfElementFileAdvancedAttachToUserMail">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ATTACH_TO_USERMAIL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedAttachToUserMail" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_ATTACHADMINMAIL')); ?>"
                                                        for="bfElementFileAdvancedAttachToAdminMail">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ATTACH_TO_ADMINMAIL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedAttachToAdminMail" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_USEURL')); ?>"
                                                        for="bfElementFileAdvancedUseUrl">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_USE_URL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedUseUrl" />
                                                </div>
                                                <div class="bfPropertyWrap" style="display:none;">
                                                    <!-- legacy and hidden as not required any longer -->
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_FILE_USEURL_DIR')); ?>"
                                                        for="bfElementFileAdvancedUseUrlDownloadDirectory">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_USE_URL_DOWNLOAD_DIRECTORY'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementFileAdvancedUseUrlDownloadDirectory" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_USE_URL_DOWNLOAD_DIRECTORY_SET_SYNCH'); ?>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementFileAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementFileAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementFileAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- FILE END -->
                                            <div id="bfHideInMailback">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_MAILBACK_HIDE')); ?>"
                                                        for="bfElementAdvancedHideInMailback">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_HIDE_IN_MAILBACK'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedHideInMailback" />
                                                </div>
                                            </div>

                                            <div id="bfAdvancedLeaf">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TABINDEX')); ?>"
                                                        id="bfElementAdvancedTabIndexLabel" for="bfElementAdvancedTabIndex">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_TAB_INDEX'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementAdvancedTabIndex" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_TURNOFF')); ?>"
                                                        for="bfElementAdvancedTurnOff">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_TURN_OFF_INITIALLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedTurnOff" />
                                                </div>
                                                <div id="bfLabelPositionToggle">
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ELEMENT_LABELPOS')); ?>"
                                                            id="bfElementAdvancedLabelPositionLabel"
                                                            for="bfElementAdvancedLabelPosition">
                                                            <?php echo BFText::_('COM_BREEZINGFORMSNG_LABEL_POSITION'); ?>
                                                        </label>
                                                        <select id="bfElementAdvancedLabelPosition">
                                                            <option value="left">
                                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_LEFT'); ?>
                                                            </option>
                                                            <option value="top">
                                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_TOP'); ?>
                                                            </option>
                                                            <option value="right">
                                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_RIGHT'); ?>
                                                            </option>
                                                            <option value="bottom">
                                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_BOTTOM'); ?>
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>

                                        <fieldset id="bfInitScript" style="display:none">
                                            <br />
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_ADVANCED_ELEMENT_INITSCRIPT'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <span class="hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_INITSCRIPT')); ?>">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_TYPE') ?>
                                                    :
                                                </span>
                                                <input
                                                    onclick="JQuery('#bfInitScriptFlags').css('display', 'none');
                                                                                            JQuery('#bfInitScriptLibrary').css('display', 'none');
                                                                                            JQuery('#bfInitScriptCustom').css('display', 'none');"
                                                    type="radio" name="initType" id="bfInitTypeNone" class="bfInitType"
                                                    value="0" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_NONE') ?>
                                                <input
                                                    onclick="JQuery('#bfInitScriptFlags').css('display', '');JQuery('#bfInitScriptLibrary').css('display', '');
                                                                                            JQuery('#bfInitScriptCustom').css('display', 'none');"
                                                    type="radio" name="initType" id="bfInitTypeLibrary" class="bfInitType"
                                                    value="1" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_LIBRARY') ?>
                                                <input
                                                    onclick="JQuery('#bfInitScriptFlags').css('display', '');
                                                                                            JQuery('#bfInitScriptLibrary').css('display', 'none');JQuery('#bfInitScriptCustom').css('display', '');"
                                                    type="radio" name="initType" id="bfInitTypeCustom" class="bfInitType"
                                                    value="2" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_CUSTOM') ?>
                                            </div>
                                            <div id="bfInitScriptFlags" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <input type="checkbox" id="bfInitFormEntry" class="bfInitFormEntry"
                                                        name="bfInitFormEntry" value="1" /><label for="bfInitFormEntry"
                                                        class="bfInitFormEntryLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_FORMENTRY'); ?>
                                                    </label>
                                                    <input type="checkbox" id="bfInitPageEntry" class="bfInitPageEntry"
                                                        name="bfInitPageEntry" value="1" /><label for="bfInitPageEntry"
                                                        class="bfInitFormEntryLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_PAGEENTRY'); ?>
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="bfInitScriptLibrary" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SCRIPT') ?>:<br /> <select
                                                        id="bfInitScriptSelection"></select>
                                                </div>
                                                <br />
                                                <div id="bfInitSelectionDescription" style="white-space: pre-line;"></div>
                                            </div>

                                            <div id="bfInitScriptCustom" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <div class="hasTooltip btn btn-secondary"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_INITSCRIPT_CODEFRAMEWORK')); ?>"
                                                        style="cursor: pointer;" onclick="createInitCode()">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_CREATE_CODE_FRAMEWORK') ?>
                                                    </div>

                                                    <?php
                                                    echo self::renderCodeMirror('bfInitCode');
                                                    ?>
                                                </div>
                                            </div>
                                        </fieldset>

                                        <fieldset id="bfActionScript" style="display:none">
                                            <br />
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_ADVANCED_ELEMENT_ACTIONSCRIPT'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <span class="hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ACTIONSCRIPT')); ?>">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_TYPE') ?>
                                                    :
                                                </span>
                                                <input
                                                    onclick="JQuery('#bfActionScriptFlags').css('display', 'none');
                                                                                            JQuery('#bfActionScriptLibrary').css('display', 'none');
                                                                                            JQuery('#bfActionScriptCustom').css('display', 'none');"
                                                    type="radio" name="actionType" name="actionType" id="bfActionTypeNone"
                                                    class="bfActionType" value="0" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_NONE') ?>
                                                <input
                                                    onclick="JQuery('#bfActionScriptFlags').css('display', '');
                                                                                            JQuery('#bfActionScriptLibrary').css('display', '');
                                                                                            JQuery('#bfActionScriptCustom').css('display', 'none');"
                                                    type="radio" name="actionType" id="bfActionTypeLibrary" class="bfActionType"
                                                    value="1" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_LIBRARY') ?>
                                                <input
                                                    onclick="JQuery('#bfActionScriptFlags').css('display', '');
                                                                                            JQuery('#bfActionScriptLibrary').css('display', 'none');
                                                                                            JQuery('#bfActionScriptCustom').css('display', '');"
                                                    type="radio" name="actionType" id="bfActionTypeCustom" class="bfActionType"
                                                    value="2" />
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_CUSTOM') ?>
                                            </div>
                                            <div id="bfActionScriptFlags" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_ACTIONS') ?>:
                                                    <input style="display:none" type="checkbox" class="bfAction"
                                                        id="bfActionClick" name="bfActionClick" value="1" /><label
                                                        style="display:none" class="bfActionLabel" id="bfActionClickLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_CLICK'); ?>
                                                    </label>
                                                    <input style="display:none" type="checkbox" class="bfAction"
                                                        id="bfActionBlur" name="bfActionBlur" value="1" /><label
                                                        style="display:none" class="bfActionLabel" id="bfActionBlurLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_BLUR'); ?>
                                                    </label>
                                                    <input style="display:none" type="checkbox" class="bfAction"
                                                        id="bfActionChange" name="bfActionChange" value="1" /><label
                                                        style="display:none" class="bfActionLabel" id="bfActionChangeLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_CHANGE'); ?>
                                                    </label>
                                                    <input style="display:none" type="checkbox" class="bfAction"
                                                        id="bfActionFocus" name="bfActionFocus" value="1" /><label
                                                        style="display:none" class="bfActionLabel" id="bfActionFocusLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_FOCUS'); ?>
                                                    </label>
                                                    <input style="display:none" type="checkbox" class="bfAction"
                                                        id="bfActionSelect" name="bfActionSelect" value="1" /><label
                                                        style="display:none" class="bfActionLabel" id="bfActionSelectLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_ELEMENTS_SELECTION'); ?>
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="bfActionScriptLibrary" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SCRIPT') ?>:<br /><select
                                                        id="bfActionsScriptSelection"></select>
                                                </div>
                                                <br />
                                                <div id="bfActionsScriptSelectionDescription" style="white-space: pre-line;"></div>
                                            </div>

                                            <div id="bfActionScriptCustom" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <div class="hasTooltip btn btn-secondary"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_ACTIONSCRIPT_CODEFRAMEWORK')); ?>"
                                                        style="cursor: pointer;" onclick="createActionCode()">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_CREATE_CODE_FRAMEWORK') ?>
                                                    </div>
                                                    <?php
                                                    echo self::renderCodeMirror('bfActionCode');
                                                    ?>
                                                </div>
                                            </div>

                                        </fieldset>

                                    </div>
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

        $out = '';
        $params = array('syntax' => 'javascript');
        $editor = Editor::getInstance('codemirror');
        // XDA-GIL - 20240112 - Id mandatory for CodeMirror v6.  
        // $out .= $editor->display($name, '', '100%', 300, 40, 20, false, null, null, null, $params);
        $out .= $editor->display($name, '', '100%', 300, 40, 20, false, $name, null, null, $params);

        $out .= '';

        // XDA-GIL - 20240112 - refresh seems to not exit with CodeMirror v6.  
        /*
        <script>
            jQuery(document).ready(function () {
                setTimeout(function () {
                    Joomla.editors.instances[' . json_encode($name) . '].refresh();
                }, 500);
            });
        </script>';*/

        return $out;
    }

}
