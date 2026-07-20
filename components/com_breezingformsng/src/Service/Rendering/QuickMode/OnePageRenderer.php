<?php
/**
 * BreezingForms NG - A Joomla Forms Application
 * @version       5.0
 * @package       BreezingForms
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL- EVH
 * @license       Released under the terms of the GNU General Public License
 * */
namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

\defined('_JEXEC') or die;

use HTML_facileFormsProcessor;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Uri\Uri;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\RuntimeAssetLoader;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

class OnePageRenderer
{

    /**
     * @var HTML_facileFormsProcessor
     */
    private $p = null;
    private $dataObject = array();
    private $rootMdata = array();
    private $fading = true;
    private $fadingClass = '';
    private $fadingCall = '';
    private $useErrorAlerts = false;
    private $useDefaultErrors = false;
    private $useBalloonErrors = false;
    private $rollover = false;
    private $rolloverColor = '';
    private $toggleFields = '';
    private $hasFlashUpload = false;
    private $flashUploadTicket = '';
    private $cancelImagePath = '';
    private $uploadImagePath = '';
    private $htmltextareas = array();
    private $htmltextareasDbIds = array();
    private $language_tag = '';
    private $hasResponsiveDatePicker = false;
    private $bsVersion = '5';
    private $bsClasses = array();
    private $bsClassPrefix = '';

    function bsClass($key)
    {

        if ($this->bsVersion == '') {

            $ver = 5;
        } else {
            $ver = $this->bsVersion;
        }

        return $this->bsClasses[$ver][$key];
    }

    public static function getEditorContent($editor)
    {
        return 'Joomla.editors.instances[' . json_encode($editor) . '].getValue()';
    }

    function __construct(HTML_facileFormsProcessor $p)
    {
        $this->p = $p;
        $default = ComponentHelper::getParams('com_languages')->get('site');
        $this->language_tag = $this->p->app->getLanguage()->getTag() != $default ? $this->p->app->getLanguage()->getTag() : 'zz-ZZ';

        $this->p->app->getDocument()->getWebAssetManager()->addInlineScript('<!--');

        $this->dataObject = json_decode(bf_b64dec($this->p->formrow->template_code), true);

        $this->rootMdata = $this->dataObject['properties'];

        if (isset($this->rootMdata['themebootstrap3classpfx']) && trim($this->rootMdata['themebootstrap3classpfx']) != '') {

            $this->bsClassPrefix = $this->rootMdata['themebootstrap3classpfx'];
        } else {

            if (isset($this->rootMdata['themebootstrapUse3']) && $this->rootMdata['themebootstrapUse3'] && isset($this->rootMdata['themebootstrap3builtin']) && $this->rootMdata['themebootstrap3builtin']) {
                $this->bsClassPrefix = 'bfbs3-';
            } else {

                $this->bsClassPrefix = '';
            }
        }

        $this->bsClasses[5] = array(
            'bar' => $this->bsClassPrefix . 'progress-bar',
            'progress' => $this->bsClassPrefix . 'progress',
            'span1' => $this->bsClassPrefix . 'col-md-1',
            'span2' => $this->bsClassPrefix . 'col-md-2',
            'span3' => $this->bsClassPrefix . 'col-md-3',
            'span4' => $this->bsClassPrefix . 'col-md-4',
            'span5' => $this->bsClassPrefix . 'col-md-5',
            'span6' => $this->bsClassPrefix . 'col-md-6',
            'span7' => $this->bsClassPrefix . 'col-md-7',
            'span8' => $this->bsClassPrefix . 'col-md-8',
            'span9' => $this->bsClassPrefix . 'col-md-9',
            'span10' => $this->bsClassPrefix . 'col-md-10',
            'span11' => $this->bsClassPrefix . 'col-md-11',
            'span12' => $this->bsClassPrefix . 'col-md-12',
            'control-group' => 'mb-3',
            'control-label' => $this->bsClassPrefix . 'form-label',
            'row-fluid' => $this->bsClassPrefix . 'row',
            'icon-asterisk' => $this->bsClassPrefix . 'fas ' . $this->bsClassPrefix . 'fa-asterisk',
            'icon-question-sign' => $this->bsClassPrefix . 'fas ' . $this->bsClassPrefix . 'fa-question-circle',
            'form-actions' => 'mt-3',
            'form-actions-buttons' => 'd-flex flex-wrap gap-2',
            'btn' => $this->bsClassPrefix . 'btn',
            'btn-primary' => $this->bsClassPrefix . 'btn-primary',
            'btn-secondary' => $this->bsClassPrefix . 'btn-secondary',
            'alert' => $this->bsClassPrefix . 'alert',
            'alert-error' => $this->bsClassPrefix . 'alert-danger',
            'controls' => '',
            'form-inline' => 'bfbs5-form-inline',
            'form-group' => $this->bsClassPrefix . 'form-group',
            'well' => $this->bsClassPrefix . 'card',
            'well-small' => $this->bsClassPrefix . 'card-body',
            'hero-unit' => 'bfbs5-hero-unit',
            'pull-left' => $this->bsClassPrefix . 'float-start',
            'pull-right' => $this->bsClassPrefix . 'float-end',
            'radio' => $this->bsClassPrefix . 'form-check-label',
            'checkbox' => $this->bsClassPrefix . 'form-check-label',
            'inline' => $this->bsClassPrefix . 'form-check-inline',
            'radio-form-group' => $this->bsClassPrefix . 'radio-form-group',
            'checkbox-form-group' => $this->bsClassPrefix . 'checkbox-form-group',
            'input-append' => $this->bsClassPrefix . 'input-group',
            'input-group-btn' => $this->bsClassPrefix . '',
            'form-control' => $this->bsClassPrefix . 'form-control',
            'icon-calendar' => $this->bsClassPrefix . 'fas ' . $this->bsClassPrefix . 'fa-calendar',
            'icon-refresh' => $this->bsClassPrefix . 'fas ' . $this->bsClassPrefix . 'fa-sync',
            'icon-play' => $this->bsClassPrefix . 'fas ' . $this->bsClassPrefix . 'fa-play',
            'icon-picture' => $this->bsClassPrefix . 'fas ' . $this->bsClassPrefix . 'fa-picture',
            'img-polaroid' => $this->bsClassPrefix . 'thumbnail',
            'icon-upload' => $this->bsClassPrefix . 'fas ' . $this->bsClassPrefix . 'fa-upload',
            'nonform-control' => $this->bsClassPrefix . 'nonform-control',
            'other-form-group' => $this->bsClassPrefix . 'other-form-group',
            'custom-form-control' => $this->bsClassPrefix . 'custom-form-control',
            'input-group-text' => $this->bsClassPrefix . 'input-group-text',
            'row' => 'row',
            'form-select' => 'form-select'
        );

        if ($this->p->app->getInput()->getString('ff_applic', '') != 'mod_facileforms' && $this->p->app->getInput()->getString('ff_applic', '') != 'plg_facileforms') {
            /* translatables */
            if (isset($this->rootMdata['title_translation' . $this->language_tag]) && $this->rootMdata['title_translation' . $this->language_tag] != '') {
                $this->rootMdata['title'] = $this->rootMdata['title_translation' . $this->language_tag];
                $this->p->app->getDocument()->setTitle($this->rootMdata['title']);
            }
            /* translatables end */
        }

        $this->fading = $this->rootMdata['fadeIn'];
        $this->useErrorAlerts = $this->rootMdata['useErrorAlerts'];
        $this->useDefaultErrors = isset($this->rootMdata['useDefaultErrors']) ? $this->rootMdata['useDefaultErrors'] : false;
        $this->useBalloonErrors = isset($this->rootMdata['useBalloonErrors']) ? $this->rootMdata['useBalloonErrors'] : false;
        $this->rollover = $this->rootMdata['rollover'];
        $this->rolloverColor = $this->rootMdata['rolloverColor'];
        $this->toggleFields = $this->parseToggleFields(isset($this->rootMdata['toggleFields']) ? $this->rootMdata['toggleFields'] : '[]');

        mt_srand();
        $this->flashUploadTicket = md5(strtotime('now') . mt_rand(0, mt_getrandmax()));

        if (isset($this->rootMdata['themebootstrapUse3']) && $this->rootMdata['themebootstrapUse3']) {

            $this->bsVersion = '5';
        }

        $this->cancelImagePath = Uri::root(true) . '/components/com_breezingformsng/themes/quickmode-bootstrap' . $this->bsVersion . '/cancel.png';
        $this->uploadImagePath = Uri::root(true) . '/components/com_breezingformsng/themes/quickmode-bootstrap' . $this->bsVersion . '/upload.png';
        if (isset($this->rootMdata['themebootstrap']) && @file_exists(JPATH_SITE . '/media/breezingforms/themes-bootstrap' . $this->bsVersion . '/' . $this->rootMdata['themebootstrap'] . '/images/cancel.png')) {
            $this->cancelImagePath = Uri::root(true) . '/media/breezingforms/themes-bootstrap' . $this->bsVersion . '/' . $this->rootMdata['themebootstrap'] . '/images/cancel.png';
        }
        if (isset($this->rootMdata['themebootstrap']) && @file_exists(JPATH_SITE . '/media/breezingforms/themes-bootstrap' . $this->bsVersion . '/' . $this->rootMdata['themebootstrap'] . '/images/upload.png')) {
            $this->uploadImagePath = Uri::root(true) . '/media/breezingforms/themes-bootstrap' . $this->bsVersion . '/' . $this->rootMdata['themebootstrap'] . '/images/upload.png';
        }
    }

    function headers()
    {

        if ($this->hasFlashUpload) {
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/plupload/moxie.js');
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/plupload/plupload.js');
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-plupload-compat.js');
        }

        RuntimeAssetLoader::style($this->p->app, Uri::root(true) . '/media/com_breezingformsng/css/site/quickmode-runtime.css');

        $this->p->app->getDocument()->getWebAssetManager()->useScript('jquery');
        HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
        RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-tooltip-init.js');

        $jQuery = 'var JQuery = jQuery;' . "\n";

        RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/scrollto.js');

        if ($this->useErrorAlerts) {
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/js/sweetalert.min.js');
        }

        if ($this->useBalloonErrors) {
            RuntimeAssetLoader::style($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/validationEngine.jquery.css');
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/jquery.validationEngine-en.js');
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/jquery.validationEngine.js');
        }

        $toggleCode = '';
        if ($this->toggleFields != '[]') {
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-toggle-fields.js');
            $toggleCode = '
var toggleFieldsArray = ' . $this->toggleFields . ';
			';
        }

        $has_last_page = 'false';
        if ($this->rootMdata['lastPageThankYou']) {
            $has_last_page = 'true';

            // loading remodal
            RuntimeAssetLoader::style($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/remodal/remodal.css');
            RuntimeAssetLoader::style($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/remodal/remodal-default-theme.css');
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/remodal/remodal.min.js');
            $this->p->app->getDocument()->getWebAssetManager()->addInlineScript("\n" . '
                   function bf_remodal_close(){
                        if(typeof crbc_cart_url != "undefined"){
                            location.href = crbc_cart_url;
                        }else{
                            location.href = ' . json_encode(Uri::getInstance()->toString()) . ';
                        }
                   }
                   ' . "\n");
        }

        RuntimeAssetLoader::style($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/ladda/ladda-themeless.min.css');
        RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/ladda/spin.min.js');
        RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/ladda/ladda.min.js');
        RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/ladda/ladda.jq.min.js');

        $this->p->app->getDocument()->getWebAssetManager()->addInlineScript(
            $jQuery . '
			var inlineErrorElements = new Array();
			var bfSummarizers = new Array();
			var bfDeactivateField = new Array();
			var bfDeactivateSection = new Array();
			var bfCharsLeftLabel = ' . json_encode(Text::_('COM_BREEZINGFORMSNG_CHARS_LEFT')) . ';
                        var ladda_button = null;
                        var orig_submit_button = null;

			' . $toggleCode . '

                        JQuery(document).ready(function(){
                            // due to a glitch in Ladda, we need to clone the submit buttons 2 times and reload ladda after submit errors
                            orig_submit_button = JQuery( "#bfSubmitButton" ).clone(true);
                            JQuery(orig_submit_button).attr("id","bfSubmitButtonTmp");
                            ladda_button = JQuery( "#bfSubmitButton" ).ladda();
                            Ladda.bind("#bfSubmitButton");
                        });

                        function bf_ajax_submit(){
                            var url = JQuery("#' . $this->p->form_id . '").attr("action"); // the script where you handle the form input.
                            var posting = JQuery.post( url, JQuery("#' . $this->p->form_id . '").serialize() );

                            JQuery(".bfPage").css("pointer-events","none");
                            JQuery(".bfPage").css("opacity","0.4");
                            JQuery("#remodal-thankyou-msg").html("");

                            posting.fail(function(jqXHR, textStatus, errorThrown) {
                                ladda_button.ladda("stop");
                                var err = ' . json_encode(Text::_('COM_BREEZINGFORMSNG_ERROR')) . ';
                                alert( err + ": " + textStatus );
                                console.log(errorThrown);
                                console.log(jqXHR);
                                if(typeof crbc_cart_url != "undefined"){
                                    location.href = crbc_cart_url;
                                }else{
                                    location.href = ' . json_encode(Uri::getInstance()->toString()) . ';
                                }
                            });

                            posting.done(function( data ) {
                                JQuery("#bfSubmitMessage").css("visibility","hidden");
                                JQuery("#bfSubmitMessage").css("display","none");
                                JQuery("#bfSubmitMessage").css("z-index","999999");
                                if(' . $has_last_page . '){
                                    ladda_button.ladda("stop");
                                    var cloned = JQuery(".bfPage").last().clone();
                                    JQuery("#remodal-thankyou-msg").html(JQuery(".bfPage").last().clone().html());
                                    JQuery(cloned).remove();
                                    ff_currentpage = JQuery(".bfPage").size() + 1;
                                    var inst = JQuery("[data-remodal-id=modal]").remodal();
                                    inst.open();

                                }else{
                                    alert(' . json_encode(Text::_('COM_BREEZINGFORMSNG_PROCESS_SUBMITSUCCESS')) . ');
                                    JQuery(".bfPage").css("pointer-events","auto");
                                    JQuery(".bfPage").css("opacity","1.0");
                                    ff_currentpage = JQuery(".bfPage").size() + 1;
                                    ladda_button.ladda("stop");
                                    if(typeof crbc_cart_url != "undefined"){
                                        location.href = crbc_cart_url;
                                    }else{
                                        location.href = ' . json_encode(Uri::getInstance()->toString()) . ';
                                    }
                                }
                            });
                        }

                        function bf_restore_submitbutton(){
                            var cloned_submit = JQuery(orig_submit_button).clone(true);
                            var old_submit = JQuery( "#bfSubmitButton" ).replaceWith( JQuery(cloned_submit) );
                            JQuery(old_submit).remove();
                            JQuery(cloned_submit).attr("id","bfSubmitButton");

                            ladda_button = JQuery( "#bfSubmitButton" ).ladda();
                            Ladda.bind("#bfSubmitButton");
                        }

                        function bf_validate_submit(element, action)
                        {
                            if(typeof bfUseErrorAlerts != "undefined"){
                             JQuery(".bfErrorMessage").html("");
                             JQuery(".bfErrorMessage").css("display","none");
                            }
                            error = ff_validation(0);

                            if (error != "") {

                                if(typeof bfUseErrorAlerts == "undefined"){
                                   alert(error);
                                } else {
                                   bfShowErrors(error);
                                }

                                ff_validationFocus();

                               JQuery(".bfErrorMessage").css("display","none");
                                ff_currentpage = ff_getPageByName(ff_validationFocusName);
                                JQuery(ff_currentpage+" .bfErrorMessage").css("display","block");

                                bf_restore_submitbutton();

                            } else {

                                ff_submitForm();
                            }
                        } // ff_validate_submit

                        function bf_validate_nextpage(page)
                        {

                            if(typeof bfUseErrorAlerts != "undefined"){
                             JQuery(".bfErrorMessage").html("");
                             JQuery(".bfErrorMessage").css("display","none");
                            }

							error = ff_validation(ff_currentpage);
                            if (error != "") {
								
                               if(typeof bfUseErrorAlerts == "undefined"){
									alert(error);
                                } else {
									bfShowErrors(error);
                                   
                                } 

								ff_validationFocus("");


                                JQuery(".bfErrorMessage").css("display","none");
                                ff_currentpage = ff_getPageByName(ff_validationFocusName);
                                JQuery("#bfPage"+ff_currentpage+" .bfErrorMessage").css("display","block");
                                ladda_button.ladda("stop");

                            } else {
                                JQuery("#bfPage"+(page)).ScrollTo({offsetTop: 50});
                                ff_currentpage = page;
                                ff_initialize("pageentry");
                                JQuery("#bfPage"+ff_currentpage).css("pointer-events","auto");
                                JQuery("#bfPage"+ff_currentpage).css("opacity","1");
                            }
                        }

                        function bf_validate_prevpage(page)
                        {
                            if(typeof bfUseErrorAlerts != "undefined"){
                             JQuery(".bfErrorMessage").html("");
                             JQuery(".bfErrorMessage").css("display","none");
                            }

                            error = ff_validation(ff_currentpage);
                            if (error != "") {

                               if(typeof bfUseErrorAlerts == "undefined"){
                                   alert(error);
                                } else {
                                   bfShowErrors(error);
                                }
                                ff_validationFocus("");
								/* need to test this */
								/*
								JQuery(".bfErrorMessage").css("display","none");
                                ff_currentpage = ff_getPageByName(ff_validationFocusName);
                                JQuery("#bfPage"+ff_currentpage+" .bfErrorMessage").css("display","block");
								ladda_button.ladda("stop");
								*/

                            } else{

                                if(page > 0){
                                 JQuery("#bfPage"+(page)).ScrollTo({offsetTop: 50});
                                 ff_currentpage = page;
                                 ff_initialize("pageentry");
                                }
                            }
                        } // ff_validate_prevpage

'
        );

        RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-field-helpers-bootstrap.js');
        RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-deactivation.js');

        if ($this->fading || !$this->useErrorAlerts || $this->rollover) {
            if (!$this->useErrorAlerts) {
                $showDefaultErrors = $this->useDefaultErrors || (!$this->useDefaultErrors && !$this->useBalloonErrors);
                $this->p->app->getDocument()->getWebAssetManager()->addInlineScript(
                    'var bfUseErrorAlerts = false;' . "\n"
                    . 'var bfShowDefaultErrors = ' . ($showDefaultErrors ? 'true' : 'false') . ';' . "\n"
                    . 'var bfErrorPageScoped = true;' . "\n"
                );
                RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-error-alerts-bootstrap.js');
            }
            if ($this->fading) {
                $this->fadingClass = ' bfFadingClass';
                $this->fadingCall = 'bfFade();';
                RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-fade.js');
            }

            if ($this->rollover && trim($this->rolloverColor) != '') {
                // removed in bootstrap
            }
        }
        RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-post-init-onepage.js');
        // loading system css

            RuntimeAssetLoader::style($this->p->app, Uri::root(true) . '/components/com_breezingformsng/themes/quickmode-bootstrap' . $this->bsVersion . '/system.css');

            // loading theme
            if (isset($this->rootMdata['themebootstrap'])) {

                $vars = '';
                $themecss = '';
                $scriptjs = '';
                $scriptphp = '';

                $themecss_path = JPATH_SITE . '/media/breezingforms/themes-bootstrap' . $this->bsVersion . '/' . $this->rootMdata['themebootstrap'] . '/theme.css';
                $vars_path = JPATH_SITE . '/media/breezingforms/themes-bootstrap' . $this->bsVersion . '/' . $this->rootMdata['themebootstrap'] . '/vars.txt';
                $scriptjs_path = JPATH_SITE . '/media/breezingforms/themes-bootstrap' . $this->bsVersion . '/' . $this->rootMdata['themebootstrap'] . '/script.js';
                $scriptphp_path = JPATH_SITE . '/media/breezingforms/themes-bootstrap' . $this->bsVersion . '/' . $this->rootMdata['themebootstrap'] . '/script.php';

                if ($this->rootMdata['themebootstrap'] != '' && $this->rootMdata['themebootstrap'] != 'none' && file_exists($themecss_path)) {

                    if (file_exists($vars_path)) {
                        $vars = file_get_contents($vars_path);
                    }
                    if (file_exists($themecss_path)) {
                        $themecss = file_get_contents($themecss_path);
                    }
                    if (file_exists($scriptphp_path)) {
                        require_once($scriptphp_path);
                    }
                    if (file_exists($scriptjs_path)) {
                        $scriptjs = file_get_contents($scriptjs_path);
                    }

                    $vars = str_replace("\r", '', $vars);
                    $vars = explode("\n", $vars);
                    foreach ($vars as $var) {
                        if (trim($var)) {
                            $keyvalue = explode('=', $var);
                            if (count($keyvalue) == 2) {
                                $themecss = str_replace('{' . trim($keyvalue[0]) . '}', trim($keyvalue[1]), $themecss);
                            }
                        }
                    }

                    $manager = $this->p->app->getDocument()->getWebAssetManager();
                    $manager->addInlineStyle($themecss);
                    if ($scriptjs) {
                        $manager->addInlineScript($scriptjs);
                    }
                }
            }
    }

    public function process(&$dataObject, $parent = null, $parentPage = null, $index = 0, $childrenLength = 0, $parentFull = null)
    {
        if (isset($dataObject['attributes']) && isset($dataObject['properties'])) {

            HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

            $options = array('type' => 'normal', 'displayType' => 'breaks');
            if ($parent != null && $parent['type'] == 'section') {
                $options['type'] = $parent['bfType'];
                $options['displayType'] = $parent['displayType'];
            }
            $parentInline = false;
            $class = ' class="' . $this->fadingClass . '"';
            if ($options['displayType'] == 'inline') {
                $class = ' class="' . $this->fadingClass . '"';
                $parentInline = true;
            }

            //determine non-elements and reduce them from the children total to determine the right span sizes
            $reduce = 0;
            $parentFullChildrenLength = 0;

            if ($parentFull != null && isset($parentFull['children'])) {
                $parentFullChildrenLength = count($parentFull['children']);
                foreach ($parentFull['children'] as $child) {
                    if (!isset($child['properties']) || (isset($child['properties']) && isset($child['properties']['bfType']) && $child['properties']['bfType'] == 'bfHidden') || (isset($child['properties']) && $child['properties']['type'] != 'element' && $child['properties']['type'] != 'section')) {
                        $reduce++;
                    }
                    //if(isset($child['properties']) && isset($child['properties']['off']) && $child['properties']['off'] && $child['properties']['type'] == 'section'){
                    //    $reduce++;
                    //}
                }
            }

            $span = '';
            if (($parentFullChildrenLength - $reduce) > 0 && $parentInline) {
                switch (12 / ($parentFullChildrenLength - $reduce)) {
                    case 6:
                        $span = ' ' . $this->bsClass('span6');
                        break;
                    case 4:
                        $span = ' ' . $this->bsClass('span4');
                        break;
                    case 3:
                        $span = ' ' . $this->bsClass('span3');
                        break;
                    case 2.4:
                        $span = ' ' . $this->bsClass('span2');
                        break;
                    case 2:
                        $span = ' ' . $this->bsClass('span2');
                        break;
                }
            }

            $mdata = $dataObject['properties'];

            if ($mdata['type'] == 'page') {

                $parentPage = $mdata;
                if ($parentPage['pageNumber'] > 1) {
                    echo '</div><!-- bfPage end -->' . "\n"; // closing previous pages
                }

                $display = '';

                if ($parentPage['pageNumber'] > 1) {
                    $display = ' style="pointer-events:none;opacity:0.4"';
                }

                if ($this->p->app->getInput()->getInt('ff_page', 1) >= $parentPage['pageNumber'] && !($this->rootMdata['lastPageThankYou'] && $parentPage['pageNumber'] == count($this->dataObject['children']))) {
                    $display = ' style="pointer-events:auto;opacity:1.0"';
                } else if ($this->p->app->getInput()->getInt('ff_page', 1) < $parentPage['pageNumber'] && !($this->rootMdata['lastPageThankYou'] && $parentPage['pageNumber'] == count($this->dataObject['children']))) {
                    $display = ' style="pointer-events:none;opacity:0.4"';
                } else if ($this->rootMdata['lastPageThankYou'] && $parentPage['pageNumber'] == count($this->dataObject['children'])) {
                    $display = ' style="display:none;"';
                }

                echo '<div id="bfPage' . $parentPage['pageNumber'] . '" class="bfPage bfQuickModeOnePage"' . $display . '>' . "\n"; // opening current page

                /* translatables */
                if (isset($mdata['pageIntro_translation' . $this->language_tag]) && $mdata['pageIntro_translation' . $this->language_tag] != '') {
                    $mdata['pageIntro'] = $mdata['pageIntro_translation' . $this->language_tag];
                }
                /* translatables end */

                if (trim($mdata['pageIntro']) != '') {

                    echo '<div class="' . (isset($this->rootMdata['themebootstrapUseHeroUnit']) && $this->rootMdata['themebootstrapUseHeroUnit'] ? $this->bsClass('hero-unit') : '') . $this->fadingClass . '">' . "\n";

                    $regex = '/{loadposition\s+(.*?)}/i';
                    $introtext = $mdata['pageIntro'];

                    preg_match_all($regex, $introtext, $matches, PREG_SET_ORDER);

                    $document = $this->p->app->getDocument();
                    $renderer = $document->loadRenderer('modules');
                    $options = array('style' => 'xhtml');

                    foreach ($matches as $match) {

                        $matcheslist = explode(',', $match[1]);
                        $position = trim($matcheslist[0]);
                        $output = $renderer->render($position, $options, null);
                        $introtext = preg_replace("|$match[0]|", addcslashes($output, '\\'), $introtext, 1);
                    }

                    echo $introtext . "\n";

                    echo '</div>' . "\n";
                }

                if (!$this->useErrorAlerts) {
                    echo '<div class="bfErrorMessage ' . $this->bsClass('alert') . ' ' . $this->bsClass('alert-error') . '" style="display:none"></div>' . "\n";
                }
            } else if ($mdata['type'] == 'section') {

                if (isset($dataObject['properties']['name']) && isset($mdata['off']) && $mdata['off']) {
                    echo '<script type="text/javascript">bfRegisterDeactivatedSection(' . json_encode($dataObject['properties']['name']) . ');</script>' . "\n";
                }

                /* translatables */
                if (isset($mdata['title_translation' . $this->language_tag]) && $mdata['title_translation' . $this->language_tag] != '') {
                    $mdata['title'] = $mdata['title_translation' . $this->language_tag];
                }
                /* translatables end */

                $normal = false;

                if ($mdata['bfType'] == 'section') {
                    echo '<div' . (isset($mdata['off']) && $mdata['off'] ? ' style="display:none" ' : '') . '' . (isset($mdata['off']) && $mdata['off'] ? '' : ' class="' . $span . ' ' . $this->fadingClass . '"') . (isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != "" ? ' id="' . $dataObject['properties']['name'] . '"' : '') . '>' . "\n";

                    if (trim($mdata['title']) != '') {
                        echo '<legend>' . htmlentities(trim($mdata['title']), ENT_QUOTES, 'UTF-8') . '</legend>' . "\n";
                    }

                    echo '<div>';
                } else if ($mdata['bfType'] == 'normal') {
                    if (isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != '') {
                        $normal = true;

                        echo '<section ' . (isset($mdata['off']) && $mdata['off'] ? 'style="display:none" ' : ' class="' . $span . ' ' . $this->fadingClass . '"') . (isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != "" ? ' id="' . $dataObject['properties']['name'] . '"' : '') . '>' . "\n";
                        echo '<div>';
                    }
                }

                /* translatables */
                if (isset($mdata['description_translation' . $this->language_tag]) && $mdata['description_translation' . $this->language_tag] != '') {
                    $mdata['description'] = $mdata['description_translation' . $this->language_tag];
                }
                /* translatables end */

                if (trim($mdata['description']) != '') {
                    echo '<div class="bfSectionDescription mb-2' . (isset($this->rootMdata['themebootstrapUseWell']) && $this->rootMdata['themebootstrapUseWell'] ? ' ' . $this->bsClass('well') . ' ' . $this->bsClass('well-small') . '' : '') . '">' . "\n";

                    $regex = '/{loadposition\s+(.*?)}/i';
                    $introtext = $mdata['description'];

                    preg_match_all($regex, $introtext, $matches, PREG_SET_ORDER);

                    if ($matches) {

                        $document = $this->p->app->getDocument();
                        $renderer = $document->loadRenderer('modules');
                        $options = array('style' => 'xhtml');

                        foreach ($matches as $match) {

                            $matcheslist = explode(',', $match[1]);
                            $position = trim($matcheslist[0]);
                            $output = $renderer->render($position, $options, null);
                            $introtext = preg_replace("|$match[0]|", addcslashes($output, '\\'), $introtext, 1);
                        }
                    }

                    echo $introtext . "\n";
                    echo '</div>' . "\n";
                }
            } else if ($mdata['type'] == 'element') {

                $onclick = '';
                if (isset($mdata['actionClick']) && $mdata['actionClick'] == 1) {
                    $onclick = 'onclick="' . $mdata['actionFunctionName'] . '(this,\'click\');" ';
                }

                $onblur = '';
                if (isset($mdata['actionBlur']) && $mdata['actionBlur'] == 1) {
                    $onblur = 'onblur="' . $mdata['actionFunctionName'] . '(this,\'blur\');" ';
                }

                $onchange = '';
                if (isset($mdata['actionChange']) && $mdata['actionChange'] == 1) {
                    $onchange = 'onchange="' . $mdata['actionFunctionName'] . '(this,\'change\');" ';
                }

                $onfocus = '';
                if (isset($mdata['actionFocus']) && $mdata['actionFocus'] == 1) {
                    $onfocus = 'onfocus="' . $mdata['actionFunctionName'] . '(this,\'focus\');" ';
                }

                $onselect = '';
                if (isset($mdata['actionSelect']) && $mdata['actionSelect'] == 1) {
                    $onselect = 'onselect="' . $mdata['actionFunctionName'] . '(this,\'select\');" ';
                }

                if ($mdata['bfType'] != 'bfHidden') {
                    echo '<div ' . (isset($mdata['off']) && $mdata['off'] ? 'style="display:none" ' : '') . 'class="bfElemWrap ' . $this->bsClass('control-group') . '' . $span . (isset($mdata['off']) && $mdata['off'] ? '' : $this->fadingClass) . '" id="bfElemWrap' . $mdata['dbId'] . '">' . "\n";
                }

                $label = '';
                if (!$mdata['hideLabel']) {
                    $badge = '';

                    if (isset($mdata['theme'])) {

                        $badge = str_replace('invisible_', '', trim($mdata['theme']));
                    }

                    if (!($mdata['bfType'] == 'bfReCaptcha' && isset($mdata['invisibleCaptcha']) && $mdata['invisibleCaptcha'] && $badge != 'inline')) {

                        $maxlengthCounter = '';
                        if ($mdata['bfType'] == 'bfTextarea' && isset($mdata['maxlength']) && $mdata['maxlength'] > 0 && isset($mdata['showMaxlengthCounter']) && $mdata['showMaxlengthCounter']) {
                            $maxlengthCounter = ' <span class=***bfMaxLengthCounter*** id=***bfMaxLengthCounter' . $mdata['dbId'] . '***>(' . $mdata['maxlength'] . ' ' . Text::_('COM_BREEZINGFORMSNG_CHARS_LEFT') . ')</span>';
                        }

                        /* translatables */
                        if (isset($mdata['label_translation' . $this->language_tag]) && $mdata['label_translation' . $this->language_tag] != '') {
                            $mdata['label'] = $mdata['label_translation' . $this->language_tag];
                        }
                        if (isset($mdata['hint_translation' . $this->language_tag]) && $mdata['hint_translation' . $this->language_tag] != '') {
                            $mdata['hint'] = $mdata['hint_translation' . $this->language_tag];
                        }
                        /* translatables end */

                        $tipOpen = '';
                        $tipClose = '';
                        $labelText = trim($mdata['label']) . str_replace("***", "\"", $maxlengthCounter);
                        if (trim($mdata['hint']) != '') {
                            if (isset($this->rootMdata['joomlaHint']) && $this->rootMdata['joomlaHint']) {
                                HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
                                $content = trim($mdata['hint']);
                                $tipOpen = '<span title="' . addslashes(trim($mdata['label'])) . '::' . str_replace(
                                    array(
                                        "\n",
                                        "\r"
                                    ),
                                    array(
                                        "",
                                        ""
                                    ),
                                    htmlentities($content, ENT_QUOTES, 'UTF-8')
                                ) . '" class="editlinktip hasTooltip">';
                                $tipClose = '</span>';
                            } else {
                                $content = trim($mdata['hint']);
                                // compat
                                $explodeHint = explode('<<<style', trim($mdata['hint']));
                                if (count($explodeHint) > 1 && trim($explodeHint[0]) != '') {
                                    $content = trim($explodeHint[1]);
                                }
                                $tipOpen = '<span class="hasTooltip" title="' . HTMLHelper::tooltipText($content) . '">';
                                $tipClose = '</span>';
                            }
                        }

                        if ($tipOpen) {
                            $tipOpen = $tipOpen . '<i class="' . $this->bsClass('icon-question-sign') . '">&nbsp;</i> ';
                        }

                        $for = '';
                        if (
                            $mdata['bfType'] == 'bfTextfield' ||
                            $mdata['bfType'] == 'bfTextarea' ||
                            $mdata['bfType'] == 'bfCheckbox' ||
                            $mdata['bfType'] == 'bfNumberInput' ||
                            $mdata['bfType'] == 'bfCheckboxGroup' ||
                            $mdata['bfType'] == 'bfCalendar' ||
                            $mdata['bfType'] == 'bfCalendarResponsive' ||
                            $mdata['bfType'] == 'bfSelect' ||
                            $mdata['bfType'] == 'bfRadioGroup' ||
                            ($mdata['bfType'] == 'bfFile' && ((!isset($mdata['flashUploader']) && !isset($mdata['html5'])) || (isset($mdata['flashUploader']) && !$mdata['flashUploader']) && (isset($mdata['html5']) && !$mdata['html5'])))
                        ) {
                            $for = 'for="ff_elem' . $mdata['dbId'] . '"';
                        }

                        if ($mdata['bfType'] == 'bfCaptcha') {
                            $for = 'for="bfCaptchaEntry"';
                        } else if ($mdata['bfType'] == 'bfReCaptcha') {
                            $for = 'for="recaptcha_response_field"';
                        }
                        $required = '';
                        if ($mdata['required']) {
                            $required = ' <i class="' . $this->bsClass('icon-asterisk') . '"></i> ' . "\n";
                        }
                        $label = '<label class="' . $this->bsClass('control-label') . '' . (isset($this->rootMdata['themebootstrapLabelTop']) && $this->rootMdata['themebootstrapLabelTop'] ? ' bfLabelBlock' : '') . '" id="bfLabel' . $mdata['dbId'] . '" ' . $for . '>' . $tipOpen . str_replace("***", "\"", $labelText) . $tipClose . $required . '</label>' . "\n";
                    }
                }

                $readonly = '';
                if (isset($mdata['readonly']) && $mdata['readonly']) {
                    $readonly = 'readonly="readonly" ';
                }

                $tabIndex = '';
                if ($mdata['tabIndex'] != -1 && is_numeric($mdata['tabIndex'])) {
                    $tabIndex = 'tabindex="' . intval($mdata['tabIndex']) . '" ';
                }

                /* translatables */
                if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
                    $mdata['value'] = $mdata['value_translation' . $this->language_tag];
                }

                for ($i = 0; $i < $this->p->rowcount; $i++) {
                    $row = $this->p->rows[$i];
                    if ($mdata['bfName'] == $row->name) {

                        if (
                            (isset($mdata['value']) || isset($mdata['list']) || isset($mdata['group'])) &&
                            (
                                $mdata['bfType'] == 'bfTextfield' ||
                                $mdata['bfType'] == 'bfTextarea' ||
                                $mdata['bfType'] == 'bfCheckbox' ||
                                $mdata['bfType'] == 'bfCheckboxGroup' ||
                                $mdata['bfType'] == 'bfSubmitButton' ||
                                $mdata['bfType'] == 'bfHidden' ||
                                $mdata['bfType'] == 'bfCalendar' ||
                                $mdata['bfType'] == 'bfNumberInput' ||
                                $mdata['bfType'] == 'bfCalendarResponsive' ||
                                $mdata['bfType'] == 'bfSelect' ||
                                $mdata['bfType'] == 'bfRadioGroup'
                            )
                        ) {

                            if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
                                $mdata['value_translation' . $this->language_tag] = $this->p->replaceCode($mdata['value_translation' . $this->language_tag], "data1 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
                            }

                            if (isset($mdata['group_translation' . $this->language_tag]) && $mdata['group_translation' . $this->language_tag] != '') {
                                $mdata['group_translation' . $this->language_tag] = $this->p->replaceCode($mdata['group_translation' . $this->language_tag], "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
                            }

                            if (isset($mdata['list_translation' . $this->language_tag]) && $mdata['list_translation' . $this->language_tag] != '') {
                                $mdata['list_translation' . $this->language_tag] = $this->p->replaceCode($mdata['list_translation' . $this->language_tag], "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
                            }

                            if ($mdata['bfType'] == 'bfSelect') {
                                $mdata['list'] = $this->p->replaceCode($row->data2, "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
                            } else if ($mdata['bfType'] == 'bfCheckboxGroup' || $mdata['bfType'] == 'bfRadioGroup') {
                                $mdata['group'] = $this->p->replaceCode($row->data2, "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
                            } else {
                                $mdata['value'] = $this->p->replaceCode($row->data1, "data1 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
                            }
                        }
                        if (isset($mdata['checked']) && $mdata['bfType'] == 'bfCheckbox') {
                            $mdata['checked'] = $row->flag1 == 1 ? true : false;
                        }
                        break;
                    }
                }

                switch ($mdata['bfType']) {

                    case 'bfTextfield':
                        $type = 'text';

                        if ($mdata['password']) {
                            $type = 'password';
                        }
                        $maxlength = '';
                        if (is_numeric($mdata['maxLength'])) {
                            $maxlength = 'maxlength="' . intval($mdata['maxLength']) . '" ';
                        }
                        $size = '';
                        if ($mdata['size'] != '') {
                            $size = 'style="width:' . htmlentities(strip_tags($mdata['size'])) . ' !important; min-width:' . htmlentities(strip_tags($mdata['size'])) . ' !important;" ';
                        }
                        $icon = '';
                        if ($this->rootMdata['themebootstrapThemeEngine'] == 'bootstrap' && $this->rootMdata['themebootstrap'] == 'Azure') {
                            if (!isset($mdata['icon']) || $mdata['icon'] == '') {
                                $icon = '<i class="fa fa-pencil iconf--fumi" aria-hidden="true"></i>';
                            } else {
                                $icon = '<i class="fa ' . htmlentities($mdata['icon'], ENT_QUOTES, 'UTF-8') . ' iconf--fumi" aria-hidden="true"></i>';
                            }
                        }

                        /* translatables */
                        if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
                            $mdata['value'] = $mdata['value_translation' . $this->language_tag];
                        }

                        if (isset($mdata['placeholder_translation' . $this->language_tag]) && $mdata['placeholder_translation' . $this->language_tag] != '') {
                            $mdata['placeholder'] = $mdata['placeholder_translation' . $this->language_tag];
                        }
                        /* translatables end */

                        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
                        echo '<div class="' . $this->bsClass('form-group') . '">';
                        echo $label;
                        echo $icon;
                        echo '<input ' . (isset($mdata['placeholder']) && $mdata['placeholder'] ? 'placeholder="' . htmlentities($mdata['placeholder'], ENT_QUOTES, 'UTF-8') . '" ' : '') . 'class="' . $this->bsClass('form-control') . ' ff_elem inputbox" ' . $size . $tabIndex . $maxlength . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
                        echo '</div>';
                        echo '</div>';
                        if ($mdata['mailbackAsSender']) {
                            echo '<input type="hidden" name="mailbackSender[' . $mdata['bfName'] . ']" value="true"/>' . "\n";
                        }

                        break;

                    case 'bfTextarea':

                        $width = '';
                        if ($mdata['width'] != '') {
                            $width = 'width:' . htmlentities(strip_tags($mdata['width'])) . ' !important; min-width:' . htmlentities(strip_tags($mdata['width'])) . ' !important;';
                        }
                        $height = '';
                        if ($mdata['height'] != '') {
                            $height = 'height:' . htmlentities(strip_tags($mdata['height'])) . ';';
                        }
                        $size = '';
                        if ($height != '' || $width != '') {
                            $size = 'style="' . $width . $height . '" ';
                        }
                        $icon = '';
                        if ($this->rootMdata['themebootstrapThemeEngine'] == 'bootstrap' && $this->rootMdata['themebootstrap'] == 'Azure') {
                            if (!isset($mdata['icon']) || $mdata['icon'] == '') {
                                $icon = '<i class="fa fa-pencil iconf--fumi" aria-hidden="true"></i>';
                            } else {
                                $icon = '<i class="fa ' . htmlentities($mdata['icon'], ENT_QUOTES, 'UTF-8') . ' iconf--fumi" aria-hidden="true"></i>';
                            }
                        }

                        $onkeyup = '';
                        if (isset($mdata['maxlength']) && $mdata['maxlength'] > 0) {
                            $onkeyup = 'onkeyup="bfCheckMaxlength(' . intval($mdata['dbId']) . ', ' . intval($mdata['maxlength']) . ', ' . (isset($mdata['showMaxlengthCounter']) && $mdata['showMaxlengthCounter'] ? 'true' : 'false') . ')" ';
                        }

                        /* translatables */
                        if (isset($mdata['placeholder_translation' . $this->language_tag]) && $mdata['placeholder_translation' . $this->language_tag] != '') {
                            $mdata['placeholder'] = $mdata['placeholder_translation' . $this->language_tag];
                        }
                        if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
                            $mdata['value'] = $mdata['value_translation' . $this->language_tag];
                        }
                        /* translatables end */

                        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
                        echo '<div class="' . $this->bsClass('form-group') . '">';
                        echo $label;
                        echo $icon;
                        if (isset($mdata['is_html']) && $mdata['is_html']) {
                            echo '<div style="display: inline-block; vertical-align: top; width: ' . strip_tags($mdata['width']) . ';">';
                            $editor = Editor::getInstance('tinymce');
                            $this->htmltextareas[] = 'ff_nm_' . $mdata['bfName'] . '[]';
                            $this->htmltextareasDbIds[] = 'ff_elem' . $mdata['dbId'];
                            echo $editor->display('ff_nm_' . $mdata['bfName'] . '[]', htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8'), strip_tags($mdata['width']), strip_tags($mdata['height']), '75', '20', true, 'ff_elem' . $mdata['dbId']);
                            echo '</div>';
                            echo '<style type="text/css">.toggle-editor{display: none;}</style>';
                        } else {
                            echo '<textarea ' . (isset($mdata['placeholder']) && $mdata['placeholder'] ? 'placeholder="' . htmlentities($mdata['placeholder'], ENT_QUOTES, 'UTF-8') . '" ' : '') . ' class="' . $this->bsClass('form-control') . ' ff_elem inputbox" ' . $onkeyup . $size . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '">' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '</textarea>' . "\n";
                        }
                        echo '</div>';
                        echo '</div>';
                        break;

                    case 'bfRadioGroup':

                        /* translatables */
                        if (isset($mdata['group_translation' . $this->language_tag]) && $mdata['group_translation' . $this->language_tag] != '') {
                            $mdata['group'] = $mdata['group_translation' . $this->language_tag];
                        }
                        /* translatables end */

                        if ($mdata['group'] != '') {

                            echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
                            echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('radio-form-group') . '">';
                            echo $label;
                            echo '<span class="' . $this->bsClass('nonform-control') . '">';
                            if ($mdata['wrap']) {
                                echo '<div class="bfRadioGroupWrap" style="display: inline-block; vertical-align: top;">';
                            }
                            $mdata['group'] = str_replace("\r", '', $mdata['group']);
                            $gEx = explode("\n", $mdata['group']);
                            $lines = count($gEx);
                            for ($i = 0; $i < $lines; $i++) {
                                $idExt = $i != 0 ? '_' . $i : '';
                                $iEx = explode(";", $gEx[$i]);
                                $iCnt = count($iEx);
                                if ($iCnt == 3) {
                                    $inlineClass = $mdata['wrap'] ? '' : ' ' . $this->bsClass('inline');
                                    echo '<div class="form-check' . $inlineClass . '">';
                                    echo '<input ' . ($iEx[0] == 1 ? 'checked="checked" ' : '') . ' class="ff_elem form-check-input" ' . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . ($readonly ? ' disabled="disabled" ' : '') . 'type="radio" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . $idExt . '"/>' . "\n";
                                    echo '<label class="' . $this->bsClass('radio') . '" id="bfGroupLabel' . $mdata['dbId'] . $idExt . '" for="ff_elem' . $mdata['dbId'] . $idExt . '">' . trim($iEx[1]) . '</label>';
                                    echo '</div>';
                                }
                            }
                            if ($mdata['wrap']) {
                                echo '</div>';
                            }
                            echo '</span>';
                            echo '</div>';
                            echo '</div>';
                        }

                        break;

                    case 'bfCheckboxGroup':
                        /* translatables */
                        if (isset($mdata['group_translation' . $this->language_tag]) && $mdata['group_translation' . $this->language_tag] != '') {
                            $mdata['group'] = $mdata['group_translation' . $this->language_tag];
                        }
                        /* translatables end */
                        if ($mdata['group'] != '') {
                            echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
                            echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('radio-form-group') . '">';
                            echo $label;
                            echo '<span class="' . $this->bsClass('nonform-control') . '">';
                            if ($mdata['wrap']) {
                                echo '<div class="bfCheckboxGroupWrap" style="display: inline-block; vertical-align: top;">';
                            }
                            $mdata['group'] = str_replace("\r", '', $mdata['group']);
                            $gEx = explode("\n", $mdata['group']);
                            $lines = count($gEx);

                            for ($i = 0; $i < $lines; $i++) {
                                $idExt = $i != 0 ? '_' . $i : '';
                                $iEx = explode(";", $gEx[$i]);
                                $iCnt = count($iEx);
                                if ($iCnt == 3) {
                                    $inlineClass = $mdata['wrap'] ? '' : ' ' . $this->bsClass('inline');
                                    echo '<div class="form-check' . $inlineClass . '">';
                                    echo '<input ' . ($iEx[0] == 1 ? 'checked="checked" ' : '') . ' class="ff_elem form-check-input" ' . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . ($readonly ? ' disabled="disabled" ' : '') . 'type="checkbox" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . $idExt . '"/>' . "\n";
                                    echo '<label class="' . $this->bsClass('checkbox') . '" id="bfGroupLabel' . $mdata['dbId'] . $idExt . '" for="ff_elem' . $mdata['dbId'] . $idExt . '">' . trim($iEx[1]) . '</label>';
                                    echo '</div>';
                                }
                            }
                            if ($mdata['wrap']) {
                                echo '</div>';
                            }
                            echo '</span>';
                            echo '</div>';
                            echo '</div>';
                        }

                        break;

                    case 'bfCheckbox':
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

                        break;

                    case 'bfSelect':
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

                        break;

                    case 'bfFile':
                        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
                        echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('other-form-group') . '">';
                        echo $label;
                        echo '<span class="' . $this->bsClass('nonform-control') . '">';
                        //if( ( isset( $mdata['flashUploader'] ) && $mdata['flashUploader'] ) || ( isset( $mdata['html5'] ) && $mdata['html5'] ) ){

                        $base = explode('/', Uri::base());
                        if (isset($base[count($base) - 2]) && $base[count($base) - 2] == 'administrator') {
                            unset($base[count($base) - 2]);
                            $base = array_merge($base);
                        }
                        $base = implode('/', $base);

                        echo '<input type="hidden" id="flashUpload' . $mdata['bfName'] . '" name="flashUpload' . $mdata['bfName'] . '" value="bfFlashFileQueue' . $mdata['dbId'] . '"/>' . "\n";
                        $this->hasFlashUpload = true;
                        //allowedFileExtensions
                        $allowedExts = explode(',', $mdata['allowedFileExtensions']);
                        $allowedExtsCnt = count($allowedExts);
                        for ($i = 0; $i < $allowedExtsCnt; $i++) {
                            $allowedExts[$i] = $allowedExts[$i];
                        }
                        $exts = '';
                        if ($allowedExtsCnt != 0) {
                            $exts = implode(',', $allowedExts);
                        }
                        $bytes = (isset($mdata['flashUploaderBytes']) && is_numeric($mdata['flashUploaderBytes']) && $mdata['flashUploaderBytes'] > 0 ? "max_file_size : '" . intval($mdata['flashUploaderBytes']) . "'," : '');
                        echo "
                                                        <span id=\"bfUploadContainer" . $mdata['dbId'] . "\">
                                                            <button type=\"button\" class=\"" . $this->bsClass('btn') . " " . $this->bsClass('btn-primary') . " bfUploadButton button\" id=\"bfPickFiles" . $mdata['dbId'] . "\"><i class=\"" . $this->bsClass('icon-upload') . "\"></i></button>
							</span>
                                                        <span id=\"bfUploader" . $mdata['bfName'] . "\"></span>
                                                        <div class=\"bfFlashFileQueueClass\" id=\"bfFlashFileQueue" . $mdata['dbId'] . "\"></div>
                                                        <script type=\"text/javascript\">
                                                        <!--
							bfFlashUploaders.push('ff_elem" . $mdata['dbId'] . "');
                                                        var bfFlashFileQueue" . $mdata['dbId'] . " = {};
                                                        function bfUploadImageThumb(file) {
                                                                var img;
                                                                var thumbId = '#' + file.id + 'thumb';
                                                                var thumbEl = JQuery(thumbId).get(0);

                                                                function bfIsImage(f) {
                                                                        var name = (f && f.name) ? f.name : '';
                                                                        var ext = name.split('.').pop().toLowerCase();
                                                                        if (f && f.type && f.type.indexOf('image/') === 0) {
                                                                                return true;
                                                                        }
                                                                        return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].indexOf(ext) !== -1;
                                                                }

                                                                function bfFallbackThumb() {
                                                                        if (!thumbEl || !bfIsImage(file) || !window.FileReader) {
                                                                                return;
                                                                        }
                                                                        var nativeFile = null;
                                                                        if (file && typeof file.getNative === 'function') {
                                                                                nativeFile = file.getNative();
                                                                        }
                                                                        if (!nativeFile && file && typeof file.getSource === 'function') {
                                                                                var src = file.getSource();
                                                                                if (src && typeof src.getSource === 'function') {
                                                                                        nativeFile = src.getSource();
                                                                                }
                                                                        }
                                                                        if (!nativeFile) {
                                                                                return;
                                                                        }
                                                                        var reader = new FileReader();
                                                                        reader.onload = function(e) {
                                                                                try {
                                                                                        var imgTag = new Image();
                                                                                        imgTag.onload = function() {
                                                                                                imgTag.style.maxWidth = '100px';
                                                                                                imgTag.style.maxHeight = '60px';
                                                                                                thumbEl.innerHTML = '';
                                                                                                thumbEl.appendChild(imgTag);
                                                                                        };
                                                                                        imgTag.src = e.target.result;
                                                                                } catch (err) {}
                                                                        };
                                                                        reader.readAsDataURL(nativeFile);
                                                                }

                                                                if (window.moxie && window.moxie.image && window.moxie.image.Image && thumbEl) {
                                                                        try {
                                                                                img = new moxie.image.Image;
                                                                                img.onload = function() {
                                                                                        img.embed(thumbEl, {
                                                                                                width: 100,
                                                                                                height: 60,
                                                                                                crop: true,
                                                                                                swf_url: moxie.core.utils.Url.resolveUrl('" . $base . "components/com_breezingformsng/libraries/jquery/plupload/Moxie.swf')
                                                                                        });
                                                                                };

                                                                                img.onembedded = function() {
                                                                                        img.destroy();
                                                                                };

                                                                                img.onerror = function() {
                                                                                        bfFallbackThumb();
                                                                                };

                                                                                img.load(file.getSource());
                                                                                return;
                                                                        } catch (e) {}
                                                                }

                                                                bfFallbackThumb();
                                                        }
                                                        JQuery(document).ready(
                                                            function() {
                                                                var iOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/i) ? true : false );
                                                                var uploader = new plupload.Uploader({
                                                                        max_retries: 10,
                                                                        multi_selection: " . (isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false') . ",
                                                                        unique_names: iOS,
                                                                        chunk_size: '100kb',
                                                                        runtimes : '" . (isset($mdata['html5']) && $mdata['html5'] ? 'html5,' : '') . (isset($mdata['flashUploader']) && $mdata['flashUploader'] ? 'flash,' : '') . "html4',
                                                                        browse_button : 'bfPickFiles" . $mdata['dbId'] . "',
                                                                        container: 'bfUploadContainer" . $mdata['dbId'] . "',
                                                                        file_data_name: 'Filedata',
                                                                        multipart_params: { form: " . $this->p->form . ", itemName : '" . $mdata['bfName'] . "', bfFlashUploadTicket: '" . $this->flashUploadTicket . "', option: 'com_breezingformsng', format: 'html', flashUpload: 'true', Itemid: 0 },
                                                                        url : '" . $base . ($this->p->app->getConfig()->get('sef') && !$this->p->app->getConfig()->get('sef_rewrite') ? 'index.php/' : '') . ($this->p->app->getInput()->getCmd('lang', '') && $this->p->app->getConfig()->get('sef') ? ($this->p->app->getConfig()->get('sef_rewrite') ? 'index.php' : '') : 'index.php') . "',
                                                                        flash_swf_url : '" . $base . "components/com_breezingformsng/libraries/jquery/plupload/Moxie.swf',
                                                                        filters : [
                                                                                {title : '" . addslashes(Text::_('COM_BREEZINGFORMSNG_CHOOSE_FILE')) . "', extensions : '" . $exts . "'}
                                                                        ]
                                                                });
                                                                uploader.bind('FilesAdded', function(up, files) {
                                                                        for (var i in files) {
                                                                                if(typeof files[i].id != 'undefined' && files[i].id != null){
                                                                                    var fsize = '';
                                                                                    if(typeof files[i].size != 'undefined'){
                                                                                        fsize = '(' + plupload.formatSize(files[i].size) + ') ';
                                                                                    }
                                                                                    if(typeof bfUploadFileAdded == 'function'){
                                                                                        bfUploadFileAdded(files[i]);
                                                                                    }
                                                                                    JQuery('#bfFileQueue').append( '<div id=\"' + files[i].id + 'queue\">' + (iOS ? '' : files[i].name.replace(/[/\\?%*:|\"<>]/g, '')) + ' '+fsize+'<b></b></div>' );
                                                                                }
                                                                        }
                                                                        for (var i in files) {
                                                                            if(typeof files[i].id != 'undefined' && files[i].id != null){
                                                                                var error = false;
                                                                                var fsize = '';
                                                                                if(typeof files[i].size != 'undefined'){
                                                                                    fsize = '(' + plupload.formatSize(files[i].size) + ') ';
                                                                                }
                                                                                JQuery('#bfFlashFileQueue" . $mdata['dbId'] . "').append('<div class=\"bfFileQueueItem\" id=\"' + files[i].id + 'queueitem\"><div id=\"' + files[i].id + 'thumb\"></div><div id=\"' + files[i].id + '\"><img id=\"' + files[i].id + 'cancel\" src=\"" . $this->cancelImagePath . "\" style=\"cursor: pointer; padding-right: 10px;\" />' + (iOS ? '' : files[i].name.replace(/[/\\?%*:|\"<>]/g, '') ? files[i].name.replace(/[/\\?%*:|\"<>]/g, '') : '') + ' ' + fsize + '<b id=\"' + files[i].id + 'msg\" style=\"color:red;\"></b></div></div>');
                                                                                var file_ = files[i];
                                                                                var uploader_ = uploader;
                                                                                var bfUploaders_ = bfUploaders;
                                                                                JQuery('#' + files[i].id + 'cancel').click(
                                                                                    function(){
                                                                                        for( var i = 0; i < bfUploaders_.length; i++ ){
                                                                                            bfUploaders_[i].stop();
                                                                                        }
                                                                                        var id_ = this.id.split('cancel');
                                                                                        id_ = id_[0];
                                                                                        uploader_.removeFile(id_);
                                                                                        JQuery('#'+id_+'queue').remove();
                                                                                        JQuery('#'+id_+'queueitem').remove();
                                                                                        bfFlashUploadersLength--;
                                                                                        for( var i = 0; i < bfUploaders_.length; i++ ){
                                                                                            bfUploaders_[i].start();
                                                                                        }
                                                                                        // re-enable button if there is none left
                                                                                        if( " . (isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false') . " == false ){
                                                                                            var the_size = JQuery('#bfFlashFileQueue" . $mdata['dbId'] . " .bfFileQueueItem').size();
                                                                                            if( the_size == 0 ){
                                                                                                JQuery('#bfPickFiles" . $mdata['dbId'] . "').prop('disabled',false);
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                );
                                                                                var thebytes = " . (isset($mdata['flashUploaderBytes']) && is_numeric($mdata['flashUploaderBytes']) && $mdata['flashUploaderBytes'] > 0 ? intval($mdata['flashUploaderBytes']) : '0') . ";
                                                                                if(thebytes > 0 && typeof files[i].size != 'undefined' && files[i].size > thebytes){
                                                                                     alert(' " . addslashes(Text::_('COM_BREEZINGFORMSNG_FLASH_UPLOADER_TOO_LARGE')) . "');
                                                                                     error = true;
                                                                                }
                                                                                var ext = files[i].name.replace(/[/\\?%*:|\"<>]/g, '').split('.').pop().toLowerCase();
                                                                                var exts = '" . strtolower($exts) . "'.split(',');
                                                                                var found = 0;
                                                                                for (var x in exts){
                                                                                    if(exts[x] == ext){
                                                                                        found++;
                                                                                    }
                                                                                }
                                                                                if(found == 0){
                                                                                    alert( ' " . addslashes(Text::_('COM_BREEZINGFORMSNG_FILE_EXTENSION_NOT_ALLOWED')) . "' );
                                                                                    error = true;
                                                                                }
                                                                                if(error){
                                                                                    JQuery('#'+files[i].id+'queue').remove();
                                                                                    JQuery('#'+files[i].id+'queueitem').remove();
                                                                                }else{
                                                                                    bfFlashUploadersLength++;
                                                                                }
                                                                                bfUploadImageThumb(files[i]);
                                                                            }
                                                                        }
                                                                        // disable the button if no multi upload
                                                                        if( " . (isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false') . " == false ){
                                                                            var the_size = JQuery('#bfFlashFileQueue" . $mdata['dbId'] . " .bfFileQueueItem').size();
                                                                            if( the_size > 0 ){
                                                                                JQuery('#bfPickFiles" . $mdata['dbId'] . "').prop('disabled',true);
                                                                            }
                                                                        }
                                                                });
                                                                uploader.bind('UploadProgress', function(up, file) {
                                                                    if(typeof JQuery('#'+file.id+'queue').get(0) != 'undefined'){
                                                                        JQuery('#'+file.id+'queue').get(0).getElementsByTagName('b')[0].innerHTML = file.percent + '% <div style=\"height: 5px;width: ' + (file.percent*1.5) + 'px;background-color: #9de24f;\"></div>';
                                                                    }
                                                                });
                                                                uploader.bind('FileUploaded', function(up, file, response) {
                                                                    if(response.response!=''){
                                                                        if(response.response !== null){
                                                                            alert(response.response);
                                                                        }
                                                                    }
                                                                    JQuery('#'+file.id+'queue').remove();
                                                                });
                                                                uploader.init();
                                                                bfUploaders.push(uploader);
                                                            });
							//-->
                                                        </script>
							";
                        echo '<input class="ff_elem" ' . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="hidden" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
                        //}else{
                        //    echo '<input class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="file" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
                        //}
                        if ($mdata['attachToAdminMail']) {
                            echo '<input type="hidden" name="attachToAdminMail[' . $mdata['bfName'] . ']" value="true"/>' . "\n";
                        }
                        if ($mdata['attachToUserMail']) {
                            echo '<input type="hidden" name="attachToUserMail[' . $mdata['bfName'] . ']" value="true"/>' . "\n";
                        }
                        echo '</span>';
                        echo '</div>';
                        echo '</div>';
                        break;

                    case 'bfSubmitButton':
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
                        break;

                    case 'bfHidden':

                        echo '<input class="ff_elem" type="hidden" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
                        break;

                    case 'bfSummarize':
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
                        break;

                    case 'bfReCaptcha':

                        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '' . (isset($mdata['pubkey']) && $mdata['pubkey'] ? '' : ' ' . $this->bsClass('well') . ' ' . $this->bsClass('well-small') . '') . '">';
                        echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('other-form-group') . '">';
                        echo $label;
                        echo '<span class="' . $this->bsClass('nonform-control') . '">';
                        if (isset($mdata['pubkey']) && $mdata['pubkey'] != '') {

                            if (!isset($mdata['invisibleCaptcha']) || !$mdata['invisibleCaptcha']) {

                                $http = 'https';

                                $getLangTag = $this->p->app->getLanguage()->getTag();
                                $getLangSlug = explode('-', $getLangTag);
                                $reCaptchaLang = 'hl=' . $getLangSlug[0];

                                $size = (isset($mdata['size']) && $mdata['size'] != '') ? $mdata['size'] : 'normal';

                                RuntimeAssetLoader::script(
                                    $this->p->app,
                                    $http . '://www.google.com/recaptcha/api.js?' . $reCaptchaLang . '&onload=onloadBFNewRecaptchaCallback&render=explicit',
                                    ['data-usercentrics' => 'reCAPTCHA']
                                );
                                RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-recaptcha-visible.js');

                                echo '
                                                    <div style="display: inline-block !important; vertical-align: middle;">
                                                        <div class="' . $this->bsClass('control-group') . '">
                                                            <div class="' . $this->bsClass('controls') . '">
                                                                    <div id="newrecaptcha"></div>
                                                                </div>
                                                        </div>
                                                        <div class="g-recaptcha" data-sitekey="' . $mdata['pubkey'] . '"></div>
                                                    </div>
                                                    <script data-usercentrics="reCAPTCHA" type="text/javascript">bfInitVisibleReCaptcha(' . json_encode([
                                    'sitekey' => $mdata['pubkey'],
                                    'theme' => trim($mdata['theme']) == '' ? 'light' : trim($mdata['theme']),
                                    'size' => $size,
                                    'resetOnRerender' => false,
                                ]) . ');</script>';
                            } else
                                if (isset($mdata['invisibleCaptcha']) && $mdata['invisibleCaptcha']) {

                                    $badge = str_replace('invisible_', '', trim($mdata['theme']));

                                    if ($badge == 'inline') {
                                        echo '
                                        <div style="display: inline-block !important; vertical-align: middle;">
                                            <div class="' . $this->bsClass('control-group') . '">
                                                <div class="' . $this->bsClass('controls') . '">
                                                    <div id="bfInvisibleReCaptchaContainer"></div>
                                                    <div id="bfInvisibleReCaptcha"></div>
                                                </div>
                                            </div>
                                        </div>
                                        ';
                                    } else {
                                        echo '
                                        <div id="bfInvisibleReCaptchaContainer"></div>
                                        <div id="bfInvisibleReCaptcha"></div>
                                        ';
                                    }

                                    RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-recaptcha-invisible.js');

                                    echo '<script data-usercentrics="reCAPTCHA" type="text/javascript">bfInitInvisibleReCaptcha(' . json_encode([
                                        'sitekey' => $mdata['pubkey'],
                                        'badge' => $badge == 'red' ? '' : $badge,
                                        'hasFlashUpload' => $this->hasFlashUpload,
                                        'resetFlagOnCallback' => false,
                                    ]) . ');</script>
                                                  <script data-usercentrics="reCAPTCHA" src="https://www.google.com/recaptcha/api.js?onload=onloadBFNewRecaptchaCallback&render=explicit" async defer></script>
                                                  ';
                                }
                        } else {
                            echo '<span class="bfCaptcha">' . "\n";
                            echo 'WARNING: No public key given for ReCaptcha element!';
                            echo '</span>' . "\n";
                        }

                        echo '</span>';
                        echo '</div>';
                        echo '</div>';

                        break;

                    case 'bfNumberInput':
                        $type = 'number';

			if ($mdata['range']) {
				$type = 'range';
			}
			
                        $maxlength = '';
                        if (is_numeric($mdata['maxLength'])) {
                            $maxlength = 'max="' . intval($mdata['maxLength']) . '" ';
                        }
                        $icon = '';
                        if ($this->rootMdata['themebootstrapThemeEngine'] == 'bootstrap' && $this->rootMdata['themebootstrap'] == 'Azure') {
                            if (!isset($mdata['icon']) || $mdata['icon'] == '') {
                                $icon = '<i class="fa fa-pencil iconf--fumi" aria-hidden="true"></i>';
                            } else {
                                $icon = '<i class="fa ' . htmlentities($mdata['icon'], ENT_QUOTES, 'UTF-8') . ' iconf--fumi" aria-hidden="true"></i>';
                            }
                        }
                        /* translatables */

                        if (isset($mdata['placeholder_translation' . $this->language_tag]) && $mdata['placeholder_translation' . $this->language_tag] != '') {
                            $mdata['placeholder'] = '000';
                        }
                        /* translatables end */

                        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
                        echo '<div class="' . $this->bsClass('form-group') . '">';
                        echo $label;
                        echo $icon;
                        echo '<input ' . (isset($mdata['placeholder']) && $mdata['placeholder'] ? 'placeholder="' . htmlentities($mdata['placeholder'], ENT_QUOTES, 'UTF-8') . '" ' : '') . 'class="' . $this->bsClass('form-control') . ' ff_elem inputbox" ' . $tabIndex . $maxlength . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . '" step="' . $mdata['step'] . '" max="' . $mdata['max'] . '" min="' . $mdata['min'] . '"/>' . "\n";
                        echo '</div>';
                        echo '</div>';

                        // set size of element, number input doesn't allow size attr
                        if ($mdata['size'] != '') {
                            RuntimeAssetLoader::script($this->p->app,
                                Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-number-input.js'
                            );
                            echo '<script type="text/javascript">bfSetNumberInputWidth('
                                . json_encode((int) $mdata['dbId']) . ', ' . json_encode($mdata['size']) . ');</script>';
                        }


                        break;

                    case 'bfCaptcha':

                        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
                        echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('other-form-group') . '">';
                        echo $label;
                        echo '<span class="' . $this->bsClass('nonform-control') . '">';

                        $captcha_url = Uri::root(true)
                            . ($this->p->app->isClient('administrator') ? '/administrator' : '')
                            . '/index.php?option=com_breezingformsng&bfCaptcha=1';

                        echo '<div style="display: inline-block;">';

                        echo '<img alt="" ' . (isset($mdata['width']) && intval($mdata['width']) > 0 ? ' style="width: ' . intval($mdata['width']) . 'px !important;min-width: ' . intval($mdata['width']) . 'px !important;max-width: ' . intval($mdata['width']) . 'px !important;"' : 'style="width: 230px !important;min-width: 230px !important;max-width: 230px !important;"') . ' id="ff_capimgValue" class="ff_capimg ' . $this->bsClass('img-polaroid') . '" src="' . $captcha_url . '"/>' . "\n";
                        echo '<div style="height: 10px;"></div>';
                        echo '<div class="' . $this->bsClass('input-append') . '">';
                        echo '<input ' . (isset($mdata['width']) && intval($mdata['width']) > 0 ? ' style="width:' . (intval($mdata['width']) - 45) . 'px !important;min-width:' . (intval($mdata['width']) - 45) . 'px !important;max-width:' . (intval($mdata['width']) - 45) . 'px !important;"' : '') . ' autocomplete="off" class="' . $this->bsClass('form-control') . ' ' . $this->bsClass('custom-form-control') . ' ff_elem bfCaptchaField" type="text" name="bfCaptchaEntry" id="bfCaptchaEntry" />' . "\n";
                        echo '<button type="button" class="ff_elem ' . $this->bsClass('btn') . ' ' . $this->bsClass('btn-primary') . ' button" onclick="document.getElementById(\'bfCaptchaEntry\').value=\'\';document.getElementById(\'bfCaptchaEntry\').focus();document.getElementById(\'ff_capimgValue\').src = \'' . $captcha_url . '&bfMathRandom=\' + Math.random(); return false"><i class="' . $this->bsClass('icon-refresh') . '"></i></button>' . "\n";
                        echo '</div>';
                        echo '</div>';

                        echo '</span>';
                        echo '</div>';
                        echo '</div>';

                        break;

                    case 'bfCalendar':
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

                        // public static function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = array())
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
                        break;

                    case 'bfCalendarResponsive':
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
                        $mdata['format'] = $this->bfCalendarToPickadateFormat($mdata['format']);
                        $pickerFirstDay = $this->bfCalendarToPickadateFirstDay(isset($mdata['firstDay']) ? $mdata['firstDay'] : '');
                        $pickerSelectYears = $this->bfCalendarSelectYears($mdata);
                        echo '<div class="' . $this->bsClass('controls') . ' ' . $this->bsClass('form-inline') . '">';
                        echo '<div class="' . $this->bsClass('form-group') . ' ' . $this->bsClass('other-form-group') . '">';
                        echo $label;
                        echo $icon;
                        echo '<span class="' . $this->bsClass('nonform-control') . '">';

                        $size = '';
                        if ($mdata['size'] != '') {
                            $size = 'style="width:' . htmlentities(strip_tags($mdata['size'])) . '" ';
                        }

                        $exploded = explode('::', trim($mdata['value']));

                        $left = '';
                        $right = '';
                        if (count($exploded) == 2) {
                            $left = trim($exploded[0]);
                            $right = trim($exploded[1]);
                        } else {
                            $right = trim($exploded[0]);
                        }
                        if ($right === '') {
                            $right = '...';
                        }

                        echo '<div class="' . $this->bsClass('input-append') . '">';
                        echo '<input autocomplete="off" class="' . $this->bsClass('form-control') . ' ' . $this->bsClass('custom-form-control') . ' ff_elem" ' . $size . 'type="text" name="ff_nm_' . $mdata['bfName'] . '[]"  id="ff_elem' . $mdata['dbId'] . '" value="' . htmlentities($left, ENT_QUOTES, 'UTF-8') . '"/>' . "\n";
                        echo '<button style="cursor:pointer !important;" type="button" id="ff_elem' . $mdata['dbId'] . '_calendarButton" class="bfCalendar ' . $this->bsClass('btn') . ' ' . $this->bsClass('btn-primary') . ' button" value="' . htmlentities($right, ENT_QUOTES, 'UTF-8') . '"><i class="' . $this->bsClass('icon-calendar') . '"></i>' . htmlentities($right == '...' ? '' : $right, ENT_QUOTES, 'UTF-8') . '</button>' . "\n";
                        echo '</div>' . "\n";

                        if (!$this->hasResponsiveDatePicker) {
                            $this->p->app->getDocument()->getWebAssetManager()->addInlineScript(
                                'var bfPickerMinusYearIcon = ' . json_encode(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/minusyear.png') . ';'
                                . "\n" . 'var bfPickerPlusYearIcon = ' . json_encode(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/plusyear.png') . ';'
                            );
                            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-calendar-responsive-legacy-style.js');
                            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-calendar-responsive-init.js');
                        }

                        echo '<script type="text/javascript">bfInitCalendarResponsive(' . json_encode((int) $mdata['dbId']) . ', ' . json_encode([
                            'format' => $mdata['format'],
                            'selectYears' => $pickerSelectYears,
                            'firstDay' => $pickerFirstDay,
                            'hasYearScroller' => true,
                        ]) . ');</script>' . "\n";

                        $this->hasResponsiveDatePicker = true;

                        echo '</span>';
                        echo '</div>';
                        echo '</div>';
                        break;

                    case 'bfSignature':

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

                        break;

                    case 'bfStripe':
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
                            $src = 'src="' . $mdata['image'] . '" alt="Stripe" ';
                        } else {
                            $value = 'value="Stripe" ';
                        }
                        if (isset($mdata['actionClick']) && $mdata['actionClick'] == 1) {
                            $onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Stripe\';' . $mdata['actionFunctionName'] . '(this,\'click\');" ';
                        } else {
                            $onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Stripe\';" ';
                        }
                        echo '<input class="ff_elem" ' . $value . $src . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";

                        echo '</span>';
                        echo '</div>';
                        echo '</div>';
                        break;

                    case 'bfPayPal':
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
                        break;

                    case 'bfSofortueberweisung':
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
                        break;
                }

                if (isset($mdata['bfName']) && isset($mdata['off']) && $mdata['off']) {
                    echo '<script type="text/javascript">bfRegisterDeactivatedField(' . json_encode($mdata['bfName']) . ');</script>' . "\n";
                }

                if ($mdata['bfType'] == 'bfFile') {
                    echo '<span id="ff_elem' . $mdata['dbId'] . '_files"></span>';
                }

                if ($mdata['bfType'] != 'bfHidden') {
                    echo '</div>' . "\n";
                }
            }
        }

        /**
         * Paging and wrapping of inline element containers
         */
        if (isset($dataObject['properties']) && $dataObject['properties']['type'] == 'section' && $dataObject['properties']['displayType'] == 'inline') {
            echo '<div class="bfClearfix ' . $this->bsClass('row') . '">' . "\n";
        }

        if (isset($dataObject['children']) && count($dataObject['children']) != 0) {
            $childrenAmount = count($dataObject['children']);
            for ($i = 0; $i < $childrenAmount; $i++) {
                $this->process($dataObject['children'][$i], $mdata, $parentPage, $i, $childrenAmount, $dataObject);
            }
        }

        if (isset($dataObject['properties']) && $dataObject['properties']['type'] == 'section' && $dataObject['properties']['displayType'] == 'inline') {
            echo '</div>' . "\n";
        }

        if (isset($dataObject['properties']) && $dataObject['properties']['type'] == 'section' && $dataObject['properties']['bfType'] == 'section') {
            echo '</div>'; // row-fluid
            echo '</div>' . "\n";
        } else if (isset($dataObject['properties']) && $dataObject['properties']['type'] == 'section' && $dataObject['properties']['bfType'] == 'normal') {
            if (isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != '') {
                echo '</div>'; // row-fluid
                echo '</section>' . "\n";
            }
        } else if (isset($dataObject['properties']) && $dataObject['properties']['type'] == 'page') {

            $isLastPage = false;
            if ($this->rootMdata['lastPageThankYou'] && $dataObject['properties']['pageNumber'] == count($this->dataObject['children']) && count($this->dataObject['children']) > 1) {
                $isLastPage = true;
            }

            if (!$isLastPage) {

                $last = 0;
                if ($this->rootMdata['lastPageThankYou']) {
                    $last = 1;
                }

                echo '<div class="bfClearfix ' . $this->bsClass('row') . '"></div>';
                echo '<div class="' . $this->bsClass('form-actions') . '">';
                echo '<div class="' . $this->bsClass('form-actions-buttons') . '">';

                //
                /*
                  if($this->rootMdata['pagingInclude'] && $dataObject['properties']['pageNumber'] > 1){

                  if(isset($this->rootMdata['pagingPrevLabel_translation'.$this->language_tag]) && $this->rootMdata['pagingPrevLabel_translation'.$this->language_tag] != ''){
                  $this->rootMdata['pagingPrevLabel'] = $this->rootMdata['pagingPrevLabel_translation'.$this->language_tag];
                  }

                  echo '<button type="button" class="bfPrevButton ' . $this->bsClass('btn') . ' ' . $this->bsClass('btn-primary') . ' ' . $this->bsClass('pull-left') . ' button' . $this->fadingClass . '" type="submit" onclick="bf_validate_prevpage(' . ($dataObject['properties']['pageNumber'] - 1) . ');populateSummarizers();if(typeof bfRefreshAll != \'undefined\'){bfRefreshAll();}" value="' . htmlentities(trim($this->rootMdata['pagingPrevLabel']), ENT_QUOTES, 'UTF-8') . '"><span>' . htmlentities(trim($this->rootMdata['pagingPrevLabel']), ENT_QUOTES, 'UTF-8') . '</span></button>' . "\n";
                  } */

                if ($this->rootMdata['pagingInclude'] && $dataObject['properties']['pageNumber'] < count($this->dataObject['children']) - $last) {
                    /* translatables */
                    if (isset($this->rootMdata['pagingNextLabel_translation' . $this->language_tag]) && $this->rootMdata['pagingNextLabel_translation' . $this->language_tag] != '') {
                        $this->rootMdata['pagingNextLabel'] = $this->rootMdata['pagingNextLabel_translation' . $this->language_tag];
                    }
                    /* translatables end */
                    echo '<button type="button" class="bfNextButton ' . $this->bsClass('btn') . ' ' . $this->bsClass('btn-primary') . ' ' . $this->bsClass('pull-right') . ' button' . $this->fadingClass . '" type="submit" onclick="ff_currentpage = ' . json_encode($dataObject['properties']['pageNumber']) . ';bf_validate_nextpage(' . ($dataObject['properties']['pageNumber'] + 1) . ');populateSummarizers();if(typeof bfRefreshAll != \'undefined\'){bfRefreshAll();}" value="' . htmlentities(trim($this->rootMdata['pagingNextLabel']), ENT_QUOTES, 'UTF-8') . '"><span>' . htmlentities(trim($this->rootMdata['pagingNextLabel']), ENT_QUOTES, 'UTF-8') . '</span></button>' . "\n";
                }

                $callSubmit = 'bf_validate_submit(this, \'click\')';
                if ($this->hasFlashUpload) {
                    $callSubmit = 'if(typeof bfAjaxObject101 == \'undefined\' && typeof bfReCaptchaLoaded == \'undefined\'){bfDoFlashUpload()}else{bf_validate_submit(this, \'click\')}';
                }
                if ($this->rootMdata['submitInclude'] && $dataObject['properties']['pageNumber'] + 1 > count($this->dataObject['children']) - $last) {
                    /* translatables */
                    if (isset($this->rootMdata['submitLabel_translation' . $this->language_tag]) && $this->rootMdata['submitLabel_translation' . $this->language_tag] != '') {
                        $this->rootMdata['submitLabel'] = $this->rootMdata['submitLabel_translation' . $this->language_tag];
                    }
                    /* translatables end */
                    echo '<button data-style="zoom-in" type="button" id="bfSubmitButton" class="bfSubmitButton ' . $this->bsClass('btn') . ' ' . $this->bsClass('btn-primary') . ' ' . $this->bsClass('pull-right') . ' button' . $this->fadingClass . '" onclick="this.disabled=true;if(typeof bf_htmltextareainit != \'undefined\'){ bf_htmltextareainit() }if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};' . $callSubmit . ';" value="' . htmlentities(trim($this->rootMdata['submitLabel']), ENT_QUOTES, 'UTF-8') . '"><span>' . htmlentities(trim($this->rootMdata['submitLabel']), ENT_QUOTES, 'UTF-8') . '</span></button>' . "\n";
                }

                if ($this->rootMdata['cancelInclude'] && $dataObject['properties']['pageNumber'] + 1 > count($this->dataObject['children']) - $last) {
                    /* translatables */
                    if (isset($this->rootMdata['cancelLabel_translation' . $this->language_tag]) && $this->rootMdata['cancelLabel_translation' . $this->language_tag] != '') {
                        $this->rootMdata['cancelLabel'] = $this->rootMdata['cancelLabel_translation' . $this->language_tag];
                    }
                    /* translatables end */
                    echo '<button class="bfCancelButton ' . $this->bsClass('btn') . ' ' . $this->bsClass('btn-secondary') . ' ' . $this->bsClass('pull-right') . ' button' . $this->fadingClass . '" type="submit" onclick="ff_resetForm(this, \'click\');"  value="' . htmlentities(trim($this->rootMdata['cancelLabel']), ENT_QUOTES, 'UTF-8') . '"><span>' . htmlentities(trim($this->rootMdata['cancelLabel']), ENT_QUOTES, 'UTF-8') . '</span></button>' . "\n";
                }

                echo '</div>';
                echo '</div>';
            }
        }
    }

    public function render()
    {

        if (isset($this->rootMdata['themebootstrapUseProgress']) && $this->rootMdata['themebootstrapUseProgress']) {
            echo '<div class="' . $this->bsClass('progress') . '"><div id="bfProgressBar" class="' . $this->bsClass('bar') . '"></div></div>
                        <script type="text/javascript">
                        <!--
                        function bfUpdateProgress(){
                            if(ff_currentpage > 1){
                                var pages = JQuery(".bfPage").size()' . ($this->rootMdata['lastPageThankYou'] ? '-1' : '') . ';
                                var result = Math.round(((ff_currentpage-1) / pages)*100);
                                JQuery("#bfProgressBar").css("width",result+"%");
                            }else{
                                JQuery("#bfProgressBar").css("width","0%");
                            }
                        }
                        JQuery(document).ready(function(){
                            setInterval("bfUpdateProgress()", 500);
                        });
                        -->
                        </script>';
        }

        $this->process($this->dataObject);
        echo '</div>' . "\n"; // closing last page

        $this->headers();

        if ($this->hasResponsiveDatePicker) {
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/picker.js');
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/picker.date.js');

            $lang = $this->p->app->getLanguage()->getTag();
            $lang = explode('-', $lang);
            $lang = strtolower($lang[0]);
            if (file_exists(JPATH_SITE . '/components/com_breezingformsng/libraries/jquery/pickadate/translations/' . $lang . '.js')) {
                RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/translations/' . $lang . '.js');
            }

            RuntimeAssetLoader::style($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/themes/default.css');
            RuntimeAssetLoader::style($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/themes/default.date.css');
        }

        // we gonna add a blank to each textarea, since the value is transferred upon submit
        // requires a different mandatory validation than ff_valuenotempty
        $area_count = count($this->htmltextareas);
        if ($area_count) {
            $editor = Editor::getInstance('tinymce');
            RuntimeAssetLoader::script($this->p->app,
                Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-html-textareas.js'
            );
            for ($i = 0; $i < $area_count; $i++) {
                $htmltextarea = $this->htmltextareas[$i];
                $dbId = $this->htmltextareasDbIds[$i];
                echo '<script type="text/javascript">bfRegisterHtmlTextarea('
                    . json_encode($htmltextarea) . ', function () { return '
                    . $this->getEditorContent($dbId) . '; });</script>';
            }
        }

        if ($this->hasFlashUpload) {
            $tickets = $this->p->app->getSession()->get('bfFlashUploadTickets', array());
            $tickets[$this->flashUploadTicket] = array(); // stores file info for later processing
	    $this->p->app->getSession()->set('bfFlashUploadTickets', $tickets);
            echo '<input type="hidden" name="bfFlashUploadTicket" value="' . $this->flashUploadTicket . '"/>' . "\n";
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/center.js');
            RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-flash-upload-onepage.js');
            echo "<div style=\"visibility:hidden;\" id=\"bfFileQueue\"></div>";
            echo "<div style=\"visibility:hidden;display:none;\" id=\"bfSubmitMessage\">" . Text::_('COM_BREEZINGFORMSNG_SUBMIT_MESSAGE') . "</div>";
        }
        echo '<noscript>Please turn on javascript to submit your data. Thank you!</noscript>' . "\n";
        RuntimeAssetLoader::script($this->p->app, Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-onepage-switchpage.js');
        if ($this->rootMdata['lastPageThankYou']) {
            echo '
                        <div class="remodal" data-remodal-id="modal">
                        <button data-remodal-action="close" class="remodal-close"></button>
                        <p id="remodal-thankyou-msg"></p>
                        <br>
                        <button data-remodal-action="confirm" class="' . $this->bsClass('btn') . ' ' . $this->bsClass('btn-primary') . '">' . Text::_('COM_BREEZINGFORMSNG_OK') . '</button>
                      </div>
                      <script type="text/javascript">
                      <!--
                      JQuery(document).ready(function(){
                        JQuery(document).on("closing", ".remodal", function (e) {
                            bf_remodal_close();
                        });
                      });
                      //-->
                      </script>
                    ';
        }
        $this->p->app->getDocument()->getWebAssetManager()->addInlineScript('//-->');
    }

    private function bfCalendarIsTruthy($mdata, $key)
    {
        return isset($mdata[$key]) && $mdata[$key] !== '' && $mdata[$key] !== '0' && $mdata[$key] !== 0 && $mdata[$key] !== false;
    }

    private function bfCalendarShowTimeEnabled($mdata)
    {
        return $this->bfCalendarIsTruthy($mdata, 'showTime');
    }

    private function bfCalendarToPickadateFormat($format)
    {
        $format = trim((string) $format);

        if ($format === '') {
            return 'yyyy-mm-dd';
        }

        $format = str_replace(
            array('%Y', '%y', '%m', '%d', '%e', '%B', '%b'),
            array('yyyy', 'yy', 'mm', 'dd', 'd', 'mmmm', 'mmm'),
            $format
        );
        $format = preg_replace('/\s*(%H|%I|%k|%l|%M|%S|%p).*/', '', $format);
        $format = trim($format);

        return $format !== '' ? $format : 'yyyy-mm-dd';
    }

    private function bfCalendarToPickadateFirstDay($firstDay)
    {
        $firstDay = (int) $firstDay;

        if ($firstDay < 1 || $firstDay > 7) {
            $firstDay = 1;
        }

        return $firstDay === 7 ? 0 : $firstDay;
    }

    private function bfCalendarSelectYears($mdata)
    {
        $minYear = (isset($mdata['minYear']) && is_numeric($mdata['minYear'])) ? max(0, (int) $mdata['minYear']) : 0;
        $maxYear = (isset($mdata['maxYear']) && is_numeric($mdata['maxYear'])) ? max(0, (int) $mdata['maxYear']) : 0;
        $range = $minYear + $maxYear;

        return $range > 0 ? max(10, $range + 1) : 60;
    }

    public function parseToggleFields($code)
    {
        /*
          example codes:

          turn on element bla if blub is on
          turn off section bla if blub is on
          turn on section bla if blub is off
          turn off element bla if blub is off

          if element opener is off set opener huhuu

          syntax:
          ACTION STATE TARGETCATEGORY TARGETNAME if SRCNAME is VALUE
         */

        $parsed = '';
        $code = str_replace("\r", '', $code);
        $lines = explode("\n", $code);
        $linesCnt = count($lines);

        for ($i = 0; $i < $linesCnt; $i++) {
            $tokens = explode(' ', trim($lines[$i]));
            $tokensCnt = count($tokens);
            if ($tokensCnt >= 8) {
                $state = '';
                // rebuilding the state as it could be a value containing blanks
                for ($j = 7; $j < $tokensCnt; $j++) {
                    if ($j + 1 < $tokensCnt) {
                        $state .= $tokens[$j] . ' ';
                    } else {
                        $state .= $tokens[$j];
                    }
                }
                $parsed .= '{ action: "' . $tokens[0] . '", state: "' . $tokens[1] . '", tCat: "' . $tokens[2] . '", tName: "' . $tokens[3] . '", statement: "' . $tokens[4] . '", sName: "' . $tokens[5] . '", condition: "' . $tokens[6] . '", value: "' . addslashes($state) . '" },';
            }
        }

        return "[" . rtrim($parsed, ",") . "]";
    }

}
