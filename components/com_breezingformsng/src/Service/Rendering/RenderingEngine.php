<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;
/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Filter\InputFilter;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\Filesystem\Path;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Log\Log;
use Exception;
use Vcmb\Component\BreezingformsNG\Site\Table\QueryColumn;
use HTML_facileFormsProcessor;
use CB\Component\Contentbuilderng\Administrator\Helper\ContentbuilderngHelper;
use CB\Component\Contentbuilderng\Administrator\Helper\FormSourceFactory;
use CB\Component\Contentbuilderng\Administrator\Service\ArticleService;
use CB\Component\Contentbuilderng\Administrator\Service\ListSupportService;
use CB\Component\Contentbuilderng\Administrator\Service\PermissionService;
use CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\TokenizedDirectoryResolver;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\BootstrapRenderer;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\ClassicRenderer;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\MobileRenderer;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\OnePageRenderer;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\RuntimeAssetLoader;

/**
 * Page header, ContentBuilder path handling and form view rendering.
 */
final class RenderingEngine
{
    private ?TokenizedDirectoryResolver $tokenizedDirectoryResolverService = null;
    private ?ProcessorHeaderRenderer $processorHeaderRendererService = null;
    private ?ContentBuilderValueScriptBuilder $contentBuilderValueScriptBuilderService = null;
    private ?ContentBuilderReadonlyScriptBuilder $contentBuilderReadonlyScriptBuilderService = null;
    private ?ContentBuilderSignatureScriptBuilder $contentBuilderSignatureScriptBuilderService = null;
    private ?ContentBuilderFileUploadScriptBuilder $contentBuilderFileUploadScriptBuilderService = null;
    private ?EditableRecordLoader $editableRecordLoaderService = null;
    private ?PostRenderScriptBuilder $postRenderScriptBuilderService = null;
    private ?PaymentMethodFieldBuilder $paymentMethodFieldBuilderService = null;
    private ?ContentBuilderTechnicalFieldsBuilder $contentBuilderTechnicalFieldsBuilderService = null;
    private ?FormRoutingFieldsBuilder $formRoutingFieldsBuilderService = null;
    private ?FormTokenFieldBuilder $formTokenFieldBuilderService = null;
    private ?FormContextFieldsBuilder $formContextFieldsBuilderService = null;
    private ?FormClosingMarkupBuilder $formClosingMarkupBuilderService = null;
    private ?FormOpeningMarkupBuilder $formOpeningMarkupBuilderService = null;
    private ?FormOptionalContextFieldsBuilder $formOptionalContextFieldsBuilderService = null;
    private ?AdditionalHiddenFieldsBuilder $additionalHiddenFieldsBuilderService = null;
    private ?PaymentProviderDetector $paymentProviderDetectorService = null;
    private ?ContentBuilderFileValueParser $contentBuilderFileValueParserService = null;
    private ?ContentBuilderFileDisplayNameBuilder $contentBuilderFileDisplayNameBuilderService = null;
    private ?ContentBuilderFlashUploadValidationBuilder $contentBuilderFlashUploadValidationBuilderService = null;
    private ?ContentBuilderSignatureFileResolver $contentBuilderSignatureFileResolverService = null;
    private ?ContentBuilderSignatureImageEncoder $contentBuilderSignatureImageEncoderService = null;
    private ?CaptchaEndpointBuilder $captchaEndpointBuilderService = null;
    private ?CaptchaValidationRowSelector $captchaValidationRowSelectorService = null;

    public function __construct(private readonly HTML_facileFormsProcessor $processor)
    {
    }

    public function header(): string
    {
        global $ff_config;

        RuntimeAssetLoader::script(
            $this->processor->app,
            Uri::root(true) . '/media/com_breezingformsng/js/facileforms.js'
        );

        return $this->processorHeaderRenderer()->render(
            [
                'ff_processor.okrun      ' => $this->processor->okrun,
                'ff_processor.ip         ' => $this->processor->ip,
                'ff_processor.agent      ' => $this->processor->agent,
                'ff_processor.browser    ' => $this->processor->browser,
                'ff_processor.opsys      ' => $this->processor->opsys,
                'ff_processor.provider   ' => $this->processor->provider,
                'ff_processor.submitted  ' => $this->processor->submitted,
                'ff_processor.form       ' => $this->processor->form,
                'ff_processor.form_id    ' => $this->processor->form_id,
                'ff_processor.page       ' => $this->processor->page,
                'ff_processor.target     ' => $this->processor->target,
                'ff_processor.runmode    ' => $this->processor->runmode,
                'ff_processor.inframe    ' => $this->processor->inframe,
                'ff_processor.inline     ' => $this->processor->inline,
                'ff_processor.template   ' => $this->processor->template,
                'ff_processor.homepage   ' => $this->processor->homepage,
                'ff_processor.mossite    ' => $this->processor->mossite,
                'ff_processor.images     ' => $this->processor->images,
                'ff_processor.border     ' => $this->processor->border,
                'ff_processor.align      ' => $this->processor->align,
                'ff_processor.top        ' => $this->processor->top,
                'ff_processor.suffix     ' => $this->processor->suffix,
                'ff_processor.status     ' => $this->processor->status,
                'ff_processor.message    ' => $this->processor->message,
                'ff_processor.record_id  ' => $this->processor->record_id,
                'ff_processor.showgrid   ' => $this->processor->showgrid,
                'ff_processor.traceBuffer' => $this->processor->traceBuffer,
            ],
            (bool) $ff_config->compress,
            fn (string $code): string => $this->processor->compressJavascript($code)
        );
    }

    // header

    public function cbCreatePathByTokens(mixed $path, array $rows, mixed $fieldName): string
    {
        $identity = $this->processor->app->getIdentity();

        return $this->tokenizedDirectoryResolver()->resolve(
            (string) $path,
            $rows,
            (string) $fieldName,
            $this->processor->findtags,
            $this->processor->replacetags,
            [
                'username' => $identity->get('username', 'anonymous'),
                'id' => $identity->get('id', 0),
                'name' => $identity->get('name', 'Anonymous'),
            ],
            (string) $this->processor->submitted,
            (string) $this->processor->app->get('offset')
        );
    }

    public function makeSafeFolder(mixed $path): string
    {
        return $this->tokenizedDirectoryResolver()->makeSafeFolder((string) $path);
    }

    private function tokenizedDirectoryResolver(): TokenizedDirectoryResolver
    {
        return $this->tokenizedDirectoryResolverService ??=
            new TokenizedDirectoryResolver($this->processor->app->getInput());
    }

    private function processorHeaderRenderer(): ProcessorHeaderRenderer
    {
        return $this->processorHeaderRendererService ??=
            new ProcessorHeaderRenderer(new JavascriptValueExporter());
    }

    private function contentBuilderValueScriptBuilder(): ContentBuilderValueScriptBuilder
    {
        return $this->contentBuilderValueScriptBuilderService ??= new ContentBuilderValueScriptBuilder();
    }

    private function contentBuilderReadonlyScriptBuilder(): ContentBuilderReadonlyScriptBuilder
    {
        return $this->contentBuilderReadonlyScriptBuilderService ??= new ContentBuilderReadonlyScriptBuilder();
    }

    private function contentBuilderSignatureScriptBuilder(): ContentBuilderSignatureScriptBuilder
    {
        return $this->contentBuilderSignatureScriptBuilderService ??= new ContentBuilderSignatureScriptBuilder();
    }

    private function contentBuilderFileUploadScriptBuilder(): ContentBuilderFileUploadScriptBuilder
    {
        return $this->contentBuilderFileUploadScriptBuilderService ??= new ContentBuilderFileUploadScriptBuilder();
    }

    private function editableRecordLoader(): EditableRecordLoader
    {
        return $this->editableRecordLoaderService ??= new EditableRecordLoader($this->processor->database);
    }

    private function postRenderScriptBuilder(): PostRenderScriptBuilder
    {
        return $this->postRenderScriptBuilderService ??= new PostRenderScriptBuilder();
    }

    private function paymentMethodFieldBuilder(): PaymentMethodFieldBuilder
    {
        return $this->paymentMethodFieldBuilderService ??= new PaymentMethodFieldBuilder();
    }

    private function contentBuilderTechnicalFieldsBuilder(): ContentBuilderTechnicalFieldsBuilder
    {
        return $this->contentBuilderTechnicalFieldsBuilderService ??= new ContentBuilderTechnicalFieldsBuilder();
    }

    private function formRoutingFieldsBuilder(): FormRoutingFieldsBuilder
    {
        return $this->formRoutingFieldsBuilderService ??= new FormRoutingFieldsBuilder();
    }

    private function formTokenFieldBuilder(): FormTokenFieldBuilder
    {
        return $this->formTokenFieldBuilderService ??= new FormTokenFieldBuilder();
    }

    private function formContextFieldsBuilder(): FormContextFieldsBuilder
    {
        return $this->formContextFieldsBuilderService ??= new FormContextFieldsBuilder();
    }

    private function formClosingMarkupBuilder(): FormClosingMarkupBuilder
    {
        return $this->formClosingMarkupBuilderService ??= new FormClosingMarkupBuilder();
    }

    private function formOpeningMarkupBuilder(): FormOpeningMarkupBuilder
    {
        return $this->formOpeningMarkupBuilderService ??= new FormOpeningMarkupBuilder();
    }

    private function formOptionalContextFieldsBuilder(): FormOptionalContextFieldsBuilder
    {
        return $this->formOptionalContextFieldsBuilderService ??= new FormOptionalContextFieldsBuilder();
    }

    private function additionalHiddenFieldsBuilder(): AdditionalHiddenFieldsBuilder
    {
        return $this->additionalHiddenFieldsBuilderService ??= new AdditionalHiddenFieldsBuilder();
    }

    private function paymentProviderDetector(): PaymentProviderDetector
    {
        return $this->paymentProviderDetectorService ??= new PaymentProviderDetector();
    }

    private function contentBuilderFileValueParser(): ContentBuilderFileValueParser
    {
        return $this->contentBuilderFileValueParserService ??= new ContentBuilderFileValueParser();
    }

    private function contentBuilderFileDisplayNameBuilder(): ContentBuilderFileDisplayNameBuilder
    {
        return $this->contentBuilderFileDisplayNameBuilderService ??= new ContentBuilderFileDisplayNameBuilder();
    }

    private function contentBuilderFlashUploadValidationBuilder(): ContentBuilderFlashUploadValidationBuilder
    {
        return $this->contentBuilderFlashUploadValidationBuilderService ??= new ContentBuilderFlashUploadValidationBuilder();
    }

    private function contentBuilderSignatureFileResolver(): ContentBuilderSignatureFileResolver
    {
        return $this->contentBuilderSignatureFileResolverService ??= new ContentBuilderSignatureFileResolver();
    }

    private function contentBuilderSignatureImageEncoder(): ContentBuilderSignatureImageEncoder
    {
        return $this->contentBuilderSignatureImageEncoderService ??= new ContentBuilderSignatureImageEncoder();
    }

    private function captchaEndpointBuilder(): CaptchaEndpointBuilder
    {
        return $this->captchaEndpointBuilderService ??= new CaptchaEndpointBuilder();
    }

    private function captchaValidationRowSelector(): CaptchaValidationRowSelector
    {
        return $this->captchaValidationRowSelectorService ??= new CaptchaValidationRowSelector();
    }

    public function cbCheckPermissions(): array
    {
        // CONTENTBUILDER BEGIN

        $cbData = null;
        $cbForm = null;
        $cbRecord = null;
        $cbFrontend = true;
        $cbFull = false;

        if (file_exists(JPATH_ADMINISTRATOR . '/components/com_contentbuilderng/com_contentbuilderng.xml')) {

            if ($this->processor->app->isClient('administrator')) {
                $cbFrontend = false;
            }

            if ($cbFrontend) {
                $this->processor->app->getLanguage()->load('com_contentbuilderng');
            } else {
                $this->processor->app->getLanguage()->load('com_contentbuilderng', JPATH_SITE . '/administrator');
            }

            $db = $this->processor->database;

            $referenceId = (int) $this->processor->form;
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__contentbuilderng_forms'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('com_breezingformsng'))
                ->where($db->quoteName('reference_id') . ' = :referenceId')
                ->where($db->quoteName('published') . ' = 1')
                ->bind(':referenceId', $referenceId, ParameterType::INTEGER);
            $db->setQuery($query);

            $cbForms = $db->loadColumn();

            // if no BF form is associated with contentbuilder, we don't need no further checks
            if (!count($cbForms)) {
                return array('form' => $cbForm, 'record' => $cbRecord, 'frontend' => $cbFrontend, 'data' => $cbData, 'full' => $cbFull);
            }

            // test if all published contentbuilder views allow creating new submissions
            if (!$this->processor->app->getInput()->getInt('cb_record_id', 0) || !$this->processor->app->getInput()->getInt('cb_form_id', 0)) {

                $permissionService = PermissionService::createFromRuntimeContext();
                $cbAuth = true;
                foreach ($cbForms as $cbFormId) {
                    $permissionService->setPermissions($cbFormId, 0, $cbFrontend ? '_fe' : '');
                    $cbAuth = $cbFrontend
                        ? $permissionService->authorizeFe('new')
                        : $permissionService->authorize('new');
                    if (!$cbAuth) {
                        break;
                    }
                }

                if (count($cbForms) && !$cbAuth) {
                    throw new Exception(Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_NEW_NOT_ALLOWED'), 403);
                }
            }

            if ($this->processor->app->getInput()->getInt('cb_form_id', 0)) {

                // test the permissions of given record
                if ($this->processor->app->getInput()->getInt('cb_record_id', 0)) {
                    (PermissionService::createFromRuntimeContext())->setPermissions($this->processor->app->getInput()->getInt('cb_form_id', 0), $this->processor->app->getInput()->getInt('cb_record_id', 0), $cbFrontend ? '_fe' : '');
                    (PermissionService::createFromRuntimeContext())->checkPermissions('edit', Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_EDIT_NOT_ALLOWED'), $cbFrontend ? '_fe' : '');
                } else {
                    (PermissionService::createFromRuntimeContext())->setPermissions($this->processor->app->getInput()->getInt('cb_form_id', 0), 0, $cbFrontend ? '_fe' : '');
                    (PermissionService::createFromRuntimeContext())->checkPermissions('new', Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_NEW_NOT_ALLOWED'), $cbFrontend ? '_fe' : '');
                }

                $cbFormId = $this->processor->app->getInput()->getInt('cb_form_id', 0);
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from($db->quoteName('#__contentbuilderng_forms'))
                    ->where($db->quoteName('id') . ' = :cbFormId')
                    ->where($db->quoteName('published') . ' = 1')
                    ->bind(':cbFormId', $cbFormId, ParameterType::INTEGER);
                $db->setQuery($query);
                $cbData = $db->loadAssoc();
                if (is_array($cbData)) {
                    $permissionService = PermissionService::createFromRuntimeContext();
                    $cbFull = $cbFrontend ? $permissionService->authorizeFe('fullarticle') : $permissionService->authorize('fullarticle');
                    $cbForm = FormSourceFactory::getForm('com_breezingformsng', $cbData['reference_id']);
                    $cbRecord = $cbForm->getRecord($this->processor->app->getInput()->getInt('cb_record_id', 0), $cbData['published_only'], $cbFrontend ? ($cbData['own_only_fe'] ? $this->processor->app->getIdentity()->get('id', 0) : -1) : ($cbData['own_only'] ? $this->processor->app->getIdentity()->get('id', 0) : -1), $cbFrontend ? $cbData['show_all_languages_fe'] : true);

                    if (!count($cbRecord) && !$this->processor->app->getInput()->getBool('cbIsNew', false)) {
                        throw new Exception(Text::_('COM_CONTENTBUILDERNG_RECORD_NOT_FOUND'), 404);
                    }
                }
            }
        }
        return array('form' => $cbForm, 'record' => $cbRecord, 'frontend' => $cbFrontend, 'data' => $cbData, 'full' => $cbFull);
        // CONTENTBUILDER END
    }

    public function view(): void
    {
        global $ff_mospath, $ff_mossite, $my;
        global $ff_config, $ff_version, $ff_comsite, $ff_otherparams;

        if (trim((string) $this->processor->formrow->template_code_processed) !== 'QuickMode') {
            echo '<div class="alert alert-warning">' . Text::_('COM_BREEZINGFORMSNG_QUICKMODE_ONLY') . '</div>';
            return;
        }

        $is_mobile_type = '';
        $rootMdata = [];

        if (trim($this->processor->formrow->template_code_processed) == 'QuickMode') {

            $this->syncMobileSessionPreference();

            require_once(JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/functions/helpers.php');

            $rootMdata = $this->loadQuickModeMetadata();
            $is_device = $this->applyMobileMode($rootMdata);

            $is_mobile_type = $this->mobileChoiceType($is_device, $rootMdata);

            if (!$this->processor->isMobile || ($this->processor->isMobile && $this->processor->app->getInput()->getString('ff_task', '') == 'submit')) {

                // nothing
            } else {

                if ($this->processor->isMobile) {
                    $quickMode = $this->createMobileRenderer($rootMdata);
                }
            }
        }

        // CONTENTBUILDER BEGIN
        $cbResult = $this->processor->cbCheckPermissions();

        $cbForm = $cbResult['form'];
        $cbRecord = $cbResult['record'];
        $cbFrontend = $cbResult['frontend'];
        $cbFull = $cbResult['full'];
        // CONTENTBUILDER END

        if (!$this->processor->okrun)
            return;
        set_error_handler('_ff_errorHandler');
        ob_start();
        echo $this->processor->header();
        $this->initializeFormRendering();

        if ($this->executeBeforeFormPiece()) {
            $this->abortViewRendering();
            return;
        }

        [$fileExtensionsCheck, $cntFiles] = $this->buildFileExtensionsCheck();

        [$captchaError, $capFunc] = $this->createCaptchaDefaults();

        $capFunc = $this->buildCaptchaScript($captchaError, $capFunc);

        echo
            '<script type="text/javascript">' . nl() .
            '<!--' . nl() .
            '' . nl() .
            $fileExtensionsCheck .
            $capFunc;

        [$library, $linked] = $this->createScriptLibraryState();

        if ($this->processor->status == '') {
            $this->linkInitialOnload($library, $linked);
        } else {
            $this->linkSubmittedOnload($library, $linked);
        } // if
        if ($this->processor->bury()) {
            $this->abortViewRendering();
            return;
        }

        // add form scripts
        if ($this->addFormScripts($library, $linked)) {
            $this->abortViewRendering();
            return;
        }

        // all element scripts & static text/HTML
        $icons = 0;
        $tooltips = 0;
        $qcheckboxes = 0;
        $qcode = '';

        for ($i = 0; $i < $this->processor->rowcount; $i++) {
            $row = &$this->processor->rows[$i];

            $this->processor->draggableDivIds[] = 'ff_div' . $row->id;

            $this->collectElementMetadata($row, $icons, $tooltips);
            if ($row->type == "Query List") {
                $this->prepareQueryListRow($row, $qcheckboxes, $qcode);
                if ($this->processor->bury()) {
                    $this->abortViewRendering();
                    return;
                }
            } // if
            if ($this->registerElementCallbacks($row, $library, $linked)) {
                unset($row);
                $this->abortViewRendering();
                return;
            }
            $this->registerStaticTextScanCallback($row, $library, $linked);
            unset($row);
            if ($this->processor->bury()) {
                $this->abortViewRendering();
                return;
            }
        } // for

        if ($icons > 0) {
            if ($this->registerIconBorderScripts($library, $linked)) {
                $this->abortViewRendering();
                return;
            }
        } // if

        if ($qcode != '') {
            $library[] = array('ff_queryCurrPage', 'var ff_queryCurrPage = new Array();');
            $library[] = array('ff_queryPageSize', 'var ff_queryPageSize = new Array();');
            $library[] = array('ff_queryCols', 'var ff_queryCols = new Array();');
            $library[] = array('ff_queryCheckbox', 'var ff_queryCheckbox = new Array();');
            $library[] = array('ff_queryHeader', 'var ff_queryHeader = new Array();');
            $library[] = array('ff_queryPagenav', 'var ff_queryPagenav = new Array();');
            $library[] = array('ff_queryRows', 'var ff_queryRows = new Array();' . nl() . $qcode);

            $library[] = array(
                'ff_selectAllQueryRows',
                'function ff_selectAllQueryRows(id,checked)' . nl() .
                '{' . nl() .
                '    if (!ff_queryCheckbox[id]) return;' . nl() .
                '    var cnt = ff_queryRows[id].length;' . nl() .
                '    var pagesize = ff_queryPageSize[id];' . nl() .
                '    if (pagesize > 0) {' . nl() .
                '        lastpage = parseInt((cnt+pagesize-1)/pagesize);' . nl() .
                '        if (lastpage == 1)' . nl() .
                '           pagesize = cnt;' . nl() .
                '        else {' . nl() .
                '            var currpage = ff_queryCurrPage[id];' . nl() .
                '            var p;' . nl() .
                '            for (p = 1; p < currpage; p++) cnt -= pagesize;' . nl() .
                '            if (cnt > pagesize) cnt = pagesize;' . nl() .
                '        } // if' . nl() .
                '    } // if' . nl() .
                '    var curr;' . nl() .
                '    for (curr = 0; curr < cnt; curr++)' . nl() .
                '        document.getElementById(\'ff_cb\'+id+\'_\'+curr).checked = checked;' . nl() .
                '    for (curr = cnt; curr < pagesize; curr++)' . nl() .
                '        document.getElementById(\'ff_cb\'+id+\'_\'+curr).checked = false;' . nl() .
                '    if (ff_queryCheckbox[id]==1)' . nl() .
                '        document.getElementById(\'ff_cb\'+id).checked = checked;' . nl() .
                '} // ff_selectAllQueryRows'
            );

            $code = 'function ff_dispQueryPage(id,page)' . nl() .
                '{' . nl() .
                '    var forced = false;' . nl() .
                '    if (arguments.length>2) forced = arguments[2];' . nl() .
                '    var qrows = ff_queryRows[id];' . nl() .
                '    var cnt = qrows.length;' . nl() .
                '    var currpage = ff_queryCurrPage[id];' . nl() .
                '    var pagesize = ff_queryPageSize[id];' . nl() .
                '    var pagenav = ff_queryPagenav[id];' . nl() .
                '    var lastpage = 1;' . nl() .
                '    if (pagesize > 0) {' . nl() .
                '        lastpage = parseInt((cnt+pagesize-1)/pagesize);' . nl() .
                '        if (lastpage == 1) pagesize = cnt;' . nl() .
                '    } // if' . nl() .
                '    if (page < 1) page = 1;' . nl() .
                '    if (page > lastpage) page = lastpage;' . nl() .
                '    if (!forced && page == currpage) return;' . nl() .
                '    var p, c;' . nl() .
                '    for (p = 1; p < page; p++) cnt -= pagesize;' . nl() .
                '    if (cnt > pagesize) cnt = pagesize;' . nl() .
                '    var start = (page-1) * pagesize;' . nl() .
                '    var rows = document.getElementById(\'ff_elem\'+id).rows;' . nl() .
                '    var cols = ff_queryCols[id];' . nl() .
                '    var checkbox = ff_queryCheckbox[id];' . nl() .
                '    var header = ff_queryHeader[id];' . nl() .
                '    for (p = 0; p < cnt; p++) {' . nl() .
                '        var qrow = qrows[start+p];' . nl() .
                '        var row = rows[header+p];' . nl() .
                '        var cc = 0;' . nl() .
                '        for (c = 0; c < cols.length; c++)' . nl() .
                '            if (cols[c]) {' . nl() .
                '                if (c==0 && checkbox>0) {' . nl() .
                '                    document.getElementById(\'ff_cb\'+id+\'_\'+p).value = qrow[c];' . nl() .
                '                    cc++;' . nl() .
                '                } else' . nl() .
                '                    row.cells[cc++].innerHTML = qrow[c];' . nl() .
                '            } // if' . nl() .
                '        row.style.display = \'\';' . nl() .
                '    } // for' . nl() .
                '    for (p = cnt; p < pagesize; p++) {' . nl() .
                '        var row = rows[p+header];' . nl() .
                '        row.style.display = \'none\';' . nl() .
                '    } // for' . nl() .
                '    if (pagenav > 0 && pagesize > 0) {' . nl() .
                '        var navi = \'\';' . nl() .
                '        if (pagenav<=4) {' . nl() .
                '            if (page>1) navi += \'<a href="javascript:ff_dispQueryPage(\'+id+\',1);">\';' . nl() .
                '            navi += \'&lt;&lt;\';' . nl() .
                '            if (pagenav<=2) navi += \' ' . Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGESTART') . '\';' . nl() .
                '            if (page>1) navi += \'<\/a>\';' . nl() .
                '            navi += \' \';' . nl() .
                '            if (page>1) navi += \'<a href="javascript:ff_dispQueryPage(\'+id+\',\'+(page-1)+\');">\';' . nl() .
                '            navi += \'&lt;\';' . nl() .
                '            if (pagenav<=2) navi += \' ' . Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGEPREV') . '\';' . nl() .
                '            if (page>1) navi += \'<\/a>\';' . nl() .
                '            navi += \' \';' . nl() .
                '        } // if' . nl() .
                '        if (pagenav % 2) {' . nl() .
                '            for (p = 1; p <= lastpage; p++)' . nl() .
                '                if (p == page) ' . nl() .
                '                    navi += p+\' \';' . nl() .
                '                else' . nl() .
                '                    navi += \'<a href="javascript:ff_dispQueryPage(\'+id+\',\'+p+\');">\'+p+\'<\/a> \';' . nl() .
                '        } // if' . nl() .
                '        if (pagenav<=4) {' . nl() .
                '            if (page<lastpage) navi += \'<a href="javascript:ff_dispQueryPage(\'+id+\',\'+(page+1)+\');">\';' . nl() .
                '            if (pagenav<=2) navi += \'' . Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGENEXT') . ' \';' . nl() .
                '            navi += \'&gt;\';' . nl() .
                '            if (page<lastpage) navi += \'<\/a>\';' . nl() .
                '            navi += \' \';' . nl() .
                '            if (page<lastpage) navi += \'<a href="javascript:ff_dispQueryPage(\'+id+\',\'+lastpage+\');">\';' . nl() .
                '            if (pagenav<=2) navi += \'' . Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGEEND') . ' \';' . nl() .
                '            navi += \'&gt;&gt;\';' . nl() .
                '            if (page<lastpage) navi += \'<\/a>\';' . nl() .
                '        } // if' . nl() .
                '        rows[header+pagesize].cells[0].innerHTML = navi;' . nl() .
                '    } // if' . nl() .
                '    ff_queryCurrPage[id] = page;' . nl();
            if ($qcheckboxes)
                $code .= '    if (checkbox) ff_selectAllQueryRows(id, false);' . nl();
            if ($this->processor->formrow->heightmode > 0)
                $code .= '    ff_resizepage(' . $this->processor->formrow->heightmode . ', ' . $this->processor->formrow->height . ');' . nl();
            if ($this->processor->inframe)
                $code .= '    parent.window.scrollTo(0,0);' . nl();
            $code .= '    window.scrollTo(0,0);' . nl() .
                '} // ff_dispQueryPage';
            $this->processor->linkcode('ff_dispQueryPage', $library, $linked, $code);
            if ($this->processor->bury()) {
                $this->abortViewRendering();
                return;
            }
        } // if

        echo '//-->' . nl() .
            '</script>' . nl();

        if ($icons > 0) {
            RuntimeAssetLoader::script(
                $this->processor->app,
                Uri::root(true) . '/components/com_breezingformsng/libraries/js/joomla.javascript.js'
            );
        }
        if ($tooltips > 0) {
            HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
        } // if

        if (!$this->processor->inline) {
            $current_url = Uri::getInstance()->toString();

            $url = ($this->processor->inframe) ? $ff_mossite . '/index.php?format=html&tmpl=component' : (($this->processor->runmode == _FF_RUNMODE_FRONTEND) ? $current_url : 'index.php?format=html' . ($this->processor->app->getInput()->getCmd('tmpl', '') ? '&tmpl=' . $this->processor->app->getInput()->getCmd('tmpl', '') : $current_url));
            $params = ' action="' . $url . '"' .
                ' method="post"' .
                ' name="' . $this->processor->form_id . '"' .
                ' id="' . $this->processor->form_id . '"' .
                ' enctype="multipart/form-data"';
            if ($this->processor->formrow->class2 != '')
                $params .= ' class="' . $this->processor->getClassName($this->processor->formrow->class2) . '"';
            echo '<form data-ajax="false" ' . $params . ' accept-charset="utf-8" onsubmit="return false;" class="bfQuickMode">' . nl();
        } // if

        $js = '';
        $cbJs = '';

        if ($this->processor->editable && $cbRecord === null) {
            $userId = $this->processor->app->getIdentity()->get('id', -1);
            $editableRecord = $this->editableRecordLoader()->load((int) $this->processor->form, (int) $userId);
            if ($editableRecord !== null) {
                $this->processor->record_id = $editableRecord->id;
                $recordEntries = $editableRecord->entries;
                $js = '';
                foreach ($recordEntries as $recordEntry) {

                    //$recordEntry->value = $this->processor->removeDangerousHtml($recordEntry->value);

                    /*
                      $input = $this->processor->app->getInput();
                      $input->set('cbCleanVar', $recordEntry->value);
                      $recordEntry->value = $input->getHtml('cbCleanVar'); */

                    $recordEntry->value = InputFilter::getInstance([], [], 1, 1)->clean((string) $recordEntry->value, 'html');

                    switch ($recordEntry->type) {
                        case 'Textarea':
                        case 'Text':
                        case 'Hidden Input':
                        case 'Number Input':
                        case 'Calendar':
                            $js .= $this->contentBuilderValueScriptBuilder()->build((object) [
                                'recType' => $recordEntry->type,
                                'recName' => $recordEntry->name,
                                'recElementId' => $recordEntry->element,
                                'recValue' => $recordEntry->value,
                            ], $this->processor->form);
                            break;
                        case 'Checkbox':
                            if (!empty($recordEntry->value)) {
                                $js .= 'if(document.getElementById("ff_elem' . $recordEntry->element . '") && !JQuery(document.getElementById("ff_elem' . $recordEntry->element . '")).attr("checked"))JQuery(document.getElementById("ff_elem' . $recordEntry->element . '")).click();' . "\n";
                            }
                            break;
                        case 'Checkbox Group':
                            $js .= '
							for(var i = 0;i < document.ff_form' . $this->processor->form . '.elements.length;i++){
								if(document.ff_form' . $this->processor->form . '.elements[i].type == "checkbox" && document.ff_form' . $this->processor->form . '.elements[i].name == "ff_nm_' . $recordEntry->name . '[]" && document.ff_form' . $this->processor->form . '.elements[i].value == ' . json_encode($recordEntry->value) . '){
									if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $this->processor->form . '.elements[i]).attr("checked")){
									    JQuery(document.ff_form' . $this->processor->form . '.elements[i]).click();
									}
								}
							}' . "\n";
                            break;
                        case 'Radio Button':
                        case 'Radio Group':
                            $js .= '
							for(var i = 0;i < document.ff_form' . $this->processor->form . '.elements.length;i++){
								if(document.ff_form' . $this->processor->form . '.elements[i].type == "radio" && document.ff_form' . $this->processor->form . '.elements[i].name == "ff_nm_' . $recordEntry->name . '[]" && document.ff_form' . $this->processor->form . '.elements[i].value == ' . json_encode($recordEntry->value) . '){
									if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $this->processor->form . '.elements[i]).attr("checked")){
									    JQuery(document.ff_form' . $this->processor->form . '.elements[i]).click();
									}
								}
							}' . "\n";
                            break;
                        case 'Select List':
                            $js .= 'for(var i = 0; i < document.getElementById("ff_elem' . $recordEntry->element . '").options.length; i++){
								if(document.getElementById("ff_elem' . $recordEntry->element . '").options[i].value == ' . json_encode($recordEntry->value) . '){
									if(typeof JQuery != "undefined" && !JQuery(document.getElementById("ff_elem' . $recordEntry->element . '").options[i]).attr("selected")){
									    JQuery(document.getElementById("ff_elem' . $recordEntry->element . '").options[i]).attr("selected", true).trigger("change");
									}
								}
							}' . "\n";
                            break;
                    }
                }

                echo '
				<script type="text/javascript">
                                <!--' . nl() . '
                                function bfLoadEditable(){
                                    ' . $js . '
                                    // legacy seccode removal
                                    for(var i = 0;i < document.ff_form' . $this->processor->form . '.elements.length;i++){
                                            if(document.ff_form' . $this->processor->form . '.elements[i].name == "ff_nm_seccode[]"){
                                                    document.ff_form' . $this->processor->form . '.elements[i].value = "";
                                            }
                                    }
                                }
                                ' . nl() . '//-->
				</script>
				' . nl();
            }
        }

        // CONTENTBUILDER BEGIN

        if ($cbRecord !== null) {

            $cbNonEditableFields = ListSupportService::createFromRuntimeContext()->getListNonEditableElements($cbResult['data']['id']);
            $cbFlashUploadValidationOverride = '';
            foreach ($cbRecord as $cbEntry) {
                if (!in_array($cbEntry->recElementId, $cbNonEditableFields)) {

                    //$cbEntry->recValue = $this->processor->removeDangerousHtml($cbEntry->recValue);

                    /*
                      $input = $this->processor->app->getInput();
                      $input->set('cbCleanVar', $cbEntry->recValue);
                      $cbEntry->recValue = $input->getHtml('cbCleanVar'); */

                    $cbEntry->recValue = InputFilter::getInstance([], [], 1, 1)->clean((string) $cbEntry->recValue, 'html');

                    switch ($cbEntry->recType) {
                        case 'File Upload':
                            if (trim($this->processor->formrow->template_code_processed) == 'QuickMode') {

                                if ($cbFlashUploadValidationOverride == '') {
                                    $cbJs .= $this->contentBuilderFlashUploadValidationBuilder()->build();
                                    $cbFlashUploadValidationOverride = '1';
                                }

                                $fileValue = $this->contentBuilderFileValueParser()->parse((string) $cbEntry->recValue);
                                $cbFiles = $fileValue['files'];
                                $cnt = $fileValue['count'];
                                $cbJs .= '
                                    cbFlashElemCnt["ff_elem' . $cbEntry->recElementId . '"] = ' . $cnt . ';
                                ';
                                $displayNames = [];
                                foreach ($cbFiles as $cbFile) {
                                    if (trim($cbFile)) {
                                        $displayName = $this->contentBuilderFileDisplayNameBuilder()->build(
                                            ContentbuilderngHelper::contentbuilderng_wordwrap($cbFile, 150, '<br>', true)
                                        );
                                        $displayNames[] = $displayName;
                                    }
                                }
                                $uploadControls = $this->contentBuilderFileUploadScriptBuilder()->build(
                                    (int) $cbEntry->recElementId,
                                    (string) $cbEntry->recName,
                                    $cnt,
                                    $displayNames
                                );
                                $cbOut = $uploadControls['html'];
                                $js .= $uploadControls['deactivation'];
                                $js .= '
                                                    if (document.createTextNode){
                                                        if(!document.getElementById("bfFlashFileQueue' . $cbEntry->recElementId . '")){
                                                           var mydiv = document.createElement("div");
                                                           mydiv.innerHTML = "<br/>' . $cbOut . '";
                                                           JQuery("#ff_elem' . $cbEntry->recElementId . '_files").append(mydiv);
                                                        } else {
                                                           var mydiv = document.createElement("div");
                                                           mydiv.innerHTML = "' . $cbOut . '";
                                                           mydiv.innerHTML = "<br/>" + mydiv.innerHTML;
                                                           JQuery("#bfFlashFileQueue' . $cbEntry->recElementId . '").after(mydiv);
                                                        }
                                                    }' . nl();
                            }
                            break;
                        case 'Signature':

                            $sig_path = JPATH_SITE . '/media/breezingforms/signatures/';

                            $signaturePath = $this->contentBuilderSignatureFileResolver()->resolve(
                                $sig_path,
                                (string) $cbEntry->recValue
                            );

                            if ($signaturePath !== null) {

                                $sig_encoded = $this->contentBuilderSignatureImageEncoder()->encode($signaturePath);

                                $js .= $this->contentBuilderSignatureScriptBuilder()->build(
                                    (string) $cbEntry->recName,
                                    (int) $cbEntry->recElementId,
                                    (string) $sig_encoded
                                );
                            }
                            break;
                        case 'Textarea':
                        case 'Text':
                        case 'Number Input':
                        case 'Hidden Input':
                        case 'Calendar':

                            /*
                              if($cbEntry->recType == 'Textarea'){

                              $dataObject = json_decode(bf_b64dec($this->processor->formrow->template_code), true);
                              $qmelement = $this->processor->findQuickModeElement($dataObject, $cbEntry->recName);

                              if(isset($cbEntry->recValue) && $qmelement !== null && isset($qmelement['properties']['is_html']) && $qmelement['properties']['is_html']) {

                              $cbEntry->recValue = $this->processor->removeDangerousHtml($cbEntry->recValue);
                              }
                              } */

                            if ($cbEntry->recType == 'Calendar') {
                                $js .= "setTimeout(function(){";
                            }
                            $js .= 'if(typeof JQuery != "undefined"){';
                            $js .= 'JQuery("[name=\"ff_nm_' . $cbEntry->recName . '[]\"]").val(' . json_encode($cbEntry->recValue) . ');if(typeof JQuery != "undefined")JQuery("[name=\"ff_nm_' . $cbEntry->recName . '[]\"]").trigger("change");';
                            $js .= '}else{if(document.getElementById("ff_elem' . $cbEntry->recElementId . '"))document.getElementById("ff_elem' . $cbEntry->recElementId . '").value=' . json_encode($cbEntry->recValue) . ';if(typeof JQuery != "undefined")JQuery(document.getElementById("ff_elem' . $cbEntry->recElementId . '")).trigger("change");}' . nl();
                            if ($cbEntry->recType == 'Calendar') {
                                $js .= "}, 100);";
                            }
                            break;
                        case 'Checkbox':
                        case 'Checkbox Group':
                            $cbValues = explode(',', $cbEntry->recValue);
                            foreach ($cbValues as $cbValue) {
                                $cbValue = trim($cbValue);
                                $js .= '
                                                for(var i = 0;i < document.ff_form' . $this->processor->form . '.elements.length;i++){
                                                        if(document.ff_form' . $this->processor->form . '.elements[i].type == "checkbox" && document.ff_form' . $this->processor->form . '.elements[i].name == "ff_nm_' . $cbEntry->recName . '[]" && document.ff_form' . $this->processor->form . '.elements[i].value == ' . json_encode($cbValue) . '){
                                                                if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $this->processor->form . '.elements[i]).attr("checked")){
                                                                    JQuery(document.ff_form' . $this->processor->form . '.elements[i]).click();
                                                                }
                                                        }
                                                }' . nl();
                            }
                            break;
                        case 'Radio Button':
                        case 'Radio Group':
                            $cbValues = explode(',', $cbEntry->recValue);
                            foreach ($cbValues as $cbValue) {
                                $cbValue = trim($cbValue);
                                $js .= '
                                                for(var i = 0;i < document.ff_form' . $this->processor->form . '.elements.length;i++){
                                                        if(document.ff_form' . $this->processor->form . '.elements[i].type == "radio" && document.ff_form' . $this->processor->form . '.elements[i].name == "ff_nm_' . $cbEntry->recName . '[]" && document.ff_form' . $this->processor->form . '.elements[i].value == ' . json_encode($cbValue) . '){
                                                                if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $this->processor->form . '.elements[i]).attr("checked")){
                                                                    JQuery(document.ff_form' . $this->processor->form . '.elements[i]).click();
                                                                }
                                                        }
                                                }' . nl();
                            }
                            break;
                        case 'Select List':
                            $cbValues = explode(',', $cbEntry->recValue);
                            foreach ($cbValues as $cbValue) {
                                $cbValue = trim($cbValue);
                                $js .= 'for(var i = 0; i < document.getElementById("ff_elem' . $cbEntry->recElementId . '").options.length; i++){
                                                        if(document.getElementById("ff_elem' . $cbEntry->recElementId . '").options[i].value == ' . json_encode($cbValue) . '){
                                                                if(typeof JQuery != "undefined" && !JQuery(document.getElementById("ff_elem' . $cbEntry->recElementId . '").options[i]).attr("selected")){
                                                                    JQuery(document.getElementById("ff_elem' . $cbEntry->recElementId . '").options[i]).attr("selected", true).trigger("change");
                                                                }
                                                        }
                                                }' . nl();
                            }
                            break;
                    }
                }
            }

            echo '
                    <script type="text/javascript">
                    <!--' . nl() . '
                    var cbFlashElemCnt = new Array();
                    function bfCheckUploadValidation(id, obj, deactivatable){
                        if(obj.checked){
                            cbFlashElemCnt[id]--;
                        }else{
                            cbFlashElemCnt[id]++;
                        }
                        if(cbFlashElemCnt[id] == 0){
                            bfDeactivateField[deactivatable]=false;
                        }else{
                            bfDeactivateField[deactivatable]=true;
                        }
                    }
                    ' . $cbJs . '
                    function bfLoadContentBuilderEditable(){
                        ' . $js . '
                        // legacy seccode removal
                        for(var i = 0;i < document.ff_form' . $this->processor->form . '.elements.length;i++){
                                if(document.ff_form' . $this->processor->form . '.elements[i].name == "ff_nm_seccode[]"){
                                        document.ff_form' . $this->processor->form . '.elements[i].value = "";
                                }
                        }
                    }
                    ' . nl() . '//-->
                    </script>
                    ' . nl();
        }

        $cbNonEditableFields = array();
        if ($cbForm !== null) {
            $cbNonEditableFields = ListSupportService::createFromRuntimeContext()->getListNonEditableElements($cbResult['data']['id']);
            if (count($cbNonEditableFields)) {
                $this->processor->app->getDocument()->getWebAssetManager()->addInlineScript('<!--' . nl() . 'var bfDeactivateField = new Array();' . nl() . '//-->');
                echo '<script type="text/javascript">' . nl();
                echo '<!--' . nl();
                echo $this->contentBuilderReadonlyScriptBuilder()->build($cbNonEditableFields);
                echo '//-->' . nl();
                echo '</script>' . nl();
            }
        }

        // CONTENTBUILDER END

        if (trim($this->processor->formrow->template_code_processed) == '') {

            // fixing J3 css
            $this->processor->app->getDocument()->getWebAssetManager()->addInlineStyle(
                '
             .bfFormDiv input[type=checkbox][id^="ff_elem"], input[type=radio][id^="ff_elem"]{
                vertical-align: text-bottom;
             }
             .bfFormDiv input[type=checkbox][id^="ff_elem"] + [id^="ff_lbl"], input[type=radio][id^="ff_elem"] + [id^="ff_lbl"]{
                display: inline;
                vertical-align: text-top;
             }
             '
            );

            for ($i = 0; $i < $this->processor->rowcount; $i++) {
                $row = &$this->processor->rows[$i];
                if (!is_numeric($row->width))
                    $row->width = 0;
                if (!is_numeric($row->height))
                    $row->height = 0;
                if ($row->type != 'Query List') {
                    $data1 = $this->processor->replaceCode($row->data1, "data1 of $row->name", 'e', $row->id, 0);
                    if ($this->processor->bury()) {
                        $this->abortViewRendering();
                        return;
                    }
                    $data2 = $this->processor->replaceCode($row->data2, "data2 of $row->name", 'e', $row->id, 0);
                    if ($this->processor->bury()) {
                        $this->abortViewRendering();
                        return;
                    }
                    $data3 = $this->processor->replaceCode($row->data3, "data3 of $row->name", 'e', $row->id, 0);
                    if ($this->processor->bury()) {
                        $this->abortViewRendering();
                        return;
                    }
                } // if
                $attribs = 'position:absolute;z-index:' . $i . ';';
                if ($row->posx >= 0)
                    $attribs .= 'left:' . $row->posx;
                else
                    $attribs .= 'right:' . (-$row->posx);
                if ($row->posxmode)
                    $attribs .= '%;';
                else
                    $attribs .= 'px;';
                if ($row->posy >= 0)
                    $attribs .= 'top:' . $row->posy;
                else
                    $attribs .= 'bottom:' . (-$row->posy);
                if ($row->posymode)
                    $attribs .= '%;';
                else
                    $attribs .= 'px;';
                $class1 = '';
                $class2 = '';
                if ($row->type == 'Select List') {
                    if ($row->class1 != '')
                        $class1 = ' class="' . $this->processor->getClassName($row->class1) . '"';
                    if ($row->class2 != '')
                        $class2 = ' class="' . $this->processor->getClassName($row->class2) . ' chzn-done"';
                    else
                        $class2 = ' class="chzn-done"';
                } else {
                    if ($row->class1 != '')
                        $class1 = ' class="' . $this->processor->getClassName($row->class1) . '"';
                    if ($row->class2 != '')
                        $class2 = ' class="' . $this->processor->getClassName($row->class2) . '"';
                }
                switch ($row->type) {
                    case 'Static Text/HTML':
                    case 'Rectangle':
                    case 'Image':
                        if ($row->height > 0) {
                            $attribs .= 'height:' . $row->height;
                            if ($row->heightmode)
                                $attribs .= '%;';
                            else
                                $attribs .= 'px;';
                        } // if
                    case 'Query List':
                        if ($row->width > 0) {
                            $attribs .= 'width:' . $row->width;
                            if ($row->widthmode)
                                $attribs .= '%;';
                            else
                                $attribs .= 'px;';
                        } // if
                    default:
                        break;
                } // switch
                if ($row->page != $this->processor->page)
                    $attribs .= 'visibility:hidden;';
                switch ($row->type) {
                    case 'Static Text/HTML':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . $data1 . '</div>' . nl();
                        break;
                    case 'Rectangle':
                        if ($data1 != '')
                            $attribs .= 'border:' . $data1 . ';';
                        if ($data2 != '')
                            $attribs .= 'background-color:' . $data2 . ';';
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="font-size:0px;' . $attribs . '"' . $class1 . '></div>' . nl();
                        break;
                    case 'Image':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->width > 0)
                            $attribs .= 'width="' . $row->width . '" ';
                        if ($row->height > 0)
                            $attribs .= 'height="' . $row->height . '" ';
                        echo indentc(2) . '<img id="ff_elem' . $row->id . '" src="' . $data1 . '"  alt="' . $data2 . '" border="0" ' . $attribs . $class2 . '/>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Tooltip':
                        $tooltipTitle = '<strong>' . htmlspecialchars(strip_tags(trim((string) $row->title)), ENT_QUOTES, 'UTF-8') . '</strong><br />' . str_replace(
                            ["\n", "\r"],
                            ["", ""],
                            htmlentities(trim((string) $data2), ENT_QUOTES, 'UTF-8')
                        );
                        $tooltipClass = $class1 !== ''
                            ? str_replace(' class="', ' class="hasTooltip ', $class1)
                            : ' class="hasTooltip"';
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '" title="' . $tooltipTitle . '"' . $tooltipClass . '>' . nlc();
                        switch ($row->flag1) {
                            case 0:
                                $url = $ff_mossite . '/media/com_breezingformsng/images/site/tooltip.png';
                                break;
                            case 1:
                                $url = $ff_mossite . '/media/com_breezingformsng/images/site/warning.png';
                                break;
                            default:
                                $url = $data1;
                        } // switch
                        echo indentc(2) . '<img src="' . $url . '" alt="" border="0"' . $class2 . '/>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Hidden Input':
                        echo indentc(1) . '<input id="ff_elem' . $row->id . '" type="hidden" name="ff_nm_' . $row->name . '[]" value="' . $data1 . '" />' . nl();
                        break;
                    case 'Checkbox':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->flag1)
                            $attribs .= ' checked="checked"';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        $attribs .= $this->processor->script2clause($row);
                        echo indentc(2) . '<input id="ff_elem' . $row->id . '" type="checkbox" name="ff_nm_' . $row->name . '[]" value="' . $data1 . '"' . $attribs . $class2 . '/><label id="ff_lbl' . $row->id . '" for="ff_elem' . $row->id . '"> ' . $data2 . '</label>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Radio Button':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->flag1)
                            $attribs .= ' checked="checked"';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        $attribs .= $this->processor->script2clause($row);
                        echo indentc(2) . '<input id="ff_elem' . $row->id . '" type="radio" name="ff_nm_' . $row->name . '[]" value="' . $data1 . '"' . $attribs . $class2 . '/><label id="ff_lbl' . $row->id . '" for="ff_elem' . $row->id . '"> ' . $data2 . '</label>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Regular Button':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        $attribs .= $this->processor->script2clause($row);
                        echo indentc(2) . '<input id="ff_elem' . $row->id . '" type="button" name="ff_nm_' . $row->name . '" value="' . $data2 . '"' . $attribs . $class2 . '/>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Graphic Button':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        $attribs .= $this->processor->script2clause($row);
                        echo indentc(2) . '<button id="ff_elem' . $row->id . '" type="button" name="ff_nm_' . $row->name . '" value="' . $data2 . '"' . $attribs . $class2 . '>' . nlc();
                        $attribs = '';
                        if ($row->width > 0)
                            $attribs .= 'width="' . $row->width . '" ';
                        if ($row->height > 0)
                            $attribs .= 'height="' . $row->height . '" ';
                        switch ($row->flag1) {
                            case 0: // none
                                echo indentc(3) . '<table cellpadding="0" cellspacing="6" border="0">' . nlc();
                                echo indentc(4) . '<tr><td>' . nlc();
                                echo indentc(5) . '<img id="ff_img' . $row->id . '" src="' . $data1 . '"  alt="' . $data2 . '" border="0" ' . $attribs . '/>' . nlc();
                                echo indentc(4) . '</td></tr>' . nlc();
                                echo indentc(3) . '</table>' . nlc();
                                break;
                            case 1: // below
                                echo indentc(3) . '<table cellpadding="0" cellspacing="6" border="0">' . nlc();
                                echo indentc(4) . '<tr><td nowrap style="text-align:center">' . nlc();
                                echo indentc(5) . '<img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" ' . $attribs . '/><br/>' . nlc();
                                echo indentc(5) . $data2 . nlc();
                                echo indentc(4) . '</td></tr>' . nlc();
                                echo indentc(3) . '</table>' . nlc();
                                break;
                            case 2: // above
                                echo indentc(3) . '<table cellpadding="0" cellspacing="6" border="0">' . nlc();
                                echo indentc(4) . '<tr><td nowrap style="text-align:center">' . nlc();
                                echo indentc(5) . $data2 . '<br/>' . nlc();
                                echo indentc(5) . '<img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" ' . $attribs . '/>' . nlc();
                                echo indentc(4) . '</td></tr>' . nlc();
                                echo indentc(3) . '</table>.nlc()';
                                break;
                            case 3: // left
                                echo indentc(3) . '<table cellpadding="0" cellspacing="6" border="0">' . nlc();
                                echo indentc(4) . '<tr>' . nlc();
                                echo indentc(5) . '<td>' . $data2 . '</td>' . nlc();
                                echo indentc(5) . '<td><img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" ' . $attribs . '/></td>' . nlc();
                                echo indentc(4) . '</tr>' . nlc();
                                echo indentc(3) . '</table>' . nlc();
                                break;
                            default: // assume right
                                echo indentc(3) . '<table cellpadding="0" cellspacing="6" border="0">' . nlc();
                                echo indentc(4) . '<tr>' . nlc();
                                echo indentc(5) . '<td><img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" ' . $attribs . '/></td>' . nlc();
                                echo indentc(5) . '<td>' . $data2 . '</td>' . nlc();
                                echo indentc(4) . '</tr>' . nlc();
                                echo indentc(3) . '</table>' . nlc();
                                break;
                        } // switch
                        echo indentc(2) . '</button>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Icon':
                        if ($row->flag2)
                            echo indentc(1) . '<div id="ff_div' . $row->id . '" onmouseout="ff_hideIconBorder(this);" onmouseover="ff_dispIconBorder(this);" style="padding:3px;' . $attribs . '"' . $class1 . '>' . nlc();
                        else
                            echo indentc(1) . '<div id="ff_div' . $row->id . '"  style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $swap = '';
                        if ($data3 != '')
                            $swap = 'onmouseout="MM_swapImgRestore();" onmouseover="MM_swapImage(\'ff_img' . $row->id . '\',\'\',\'' . $data3 . '\',1);" ';

                        $swap .= $this->processor->script2clause($row);
                        $attribs = '';
                        if ($row->width > 0)
                            $attribs .= 'width="' . $row->width . '" ';
                        if ($row->height > 0)
                            $attribs .= 'height="' . $row->height . '" ';
                        switch ($row->flag1) {
                            case 0: // none
                                echo indentc(2) . '<span id="ff_elem' . $row->id . '" ' . $swap . '>' . nlc();
                                echo indentc(3) . '<img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" align="middle" ' . $attribs . $class2 . '/>' . nlc();
                                echo indentc(2) . '</span>' . nlc();
                                break;
                            case 1: // below
                                echo indentc(2) . '<table id="ff_elem' . $row->id . '" cellpadding="1" cellspacing="0" border="0" ' . $swap . '>' . nlc();
                                echo indentc(3) . '<tr><td style="text-align:center;"><img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" align="middle" ' . $attribs . $class2 . '/></td></tr>' . nlc();
                                echo indentc(3) . '<tr><td style="text-align:center;">' . $data2 . '</td></tr>' . nlc();
                                echo indentc(2) . '</table>' . nlc();
                                break;
                            case 2: // above
                                echo indentc(2) . '<table id="ff_elem' . $row->id . '" cellpadding="2" cellspacing="0" border="0" ' . $swap . '>' . nlc();
                                echo indentc(3) . '<tr><td style="text-align:center;">' . $data2 . '</td></tr>' . nlc();
                                echo indentc(3) . '<tr><td style="text-align:center;"><img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" align="middle" ' . $attribs . $class2 . '/></td></tr>' . nlc();
                                echo indentc(2) . '</table>' . nlc();
                                break;
                            case 3: // left
                                echo indentc(2) . '<span id="ff_elem' . $row->id . '" ' . $swap . ' style="vertical-align:middle;">' . nlc();
                                echo indentc(3) . $data2 . ' &nbsp;<img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" align="middle" ' . $attribs . $class2 . '/>' . nlc();
                                echo indentc(2) . '</span>' . nlc();
                                break;
                            default: // assume right
                                echo indentc(2) . '<span id="ff_elem' . $row->id . '" ' . $swap . ' style="vertical-align:middle;">' . nlc();
                                echo indentc(3) . '<img id="ff_img' . $row->id . '" src="' . $data1 . '" alt="" border="0" align="middle" ' . $attribs . $class2 . '/>&nbsp; ' . $data2 . nlc();
                                echo indentc(2) . '</span>' . nlc();
                                break;
                        } // switch
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Select List':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        $styles = '';
                        if ($row->width > 0)
                            $styles .= 'width:' . $row->width . 'px;';
                        if ($row->height > 0)
                            $styles .= 'height:' . $row->height . 'px;';
                        if ($row->flag1)
                            $attribs .= ' multiple="multiple"';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        $attribs .= $this->processor->script2clause($row);
                        if ($data1 != '')
                            $attribs .= ' size="' . $data1 . '"';
                        if ($styles != '')
                            $attribs .= ' style="' . $styles . '"';
                        echo indentc(2) . '<select id="ff_elem' . $row->id . '" name="ff_nm_' . $row->name . '[]" ' . $attribs . $class2 . '>' . nlc();
                        $options = explode('\n', preg_replace('/([\\r\\n])/s', '\n', $data2));
                        $cnt = count($options);
                        for ($o = 0; $o < $cnt; $o++) {
                            $opt = explode(";", $options[$o]);
                            $selected = '';
                            switch (count($opt)) {
                                case 0:
                                    break;
                                case 1:
                                    if ($this->processor->trim($opt[0])) {
                                        $selected = '0';
                                        $value = $text = $opt[0];
                                    } // if
                                    break;
                                case 2:
                                    $selected = $opt[0];
                                    $value = $text = $opt[1];
                                    break;
                                default:
                                    $selected = $opt[0];
                                    $text = $opt[1];
                                    $value = $opt[2];
                            } // switch
                            if ($this->processor->trim($selected)) {
                                $attribs = '';
                                if ($this->processor->trim($value) != '') {
                                    if ($value == '""' || $value == "''")
                                        $value = '';
                                    $attribs .= ' value="' . htmlspecialchars($value, ENT_QUOTES) . '"';
                                } // if
                                if ($selected == 1)
                                    $attribs .= ' selected="selected"';
                                echo indentc(3) . '<option' . $attribs . '>' . htmlspecialchars(trim($text), ENT_QUOTES) . '</option>' . nlc();
                            } // if
                        } // for
                        echo indentc(2) . '</select>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Text':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->width > 0) {
                            if ($row->widthmode > 0)
                                $attribs .= ' style="width:' . $row->width . 'px;"';
                            else
                                $attribs .= ' size="' . $row->width . '"';
                        } // if
                        if ($row->height > 0)
                            $attribs .= ' maxlength="' . $row->height . '"';
                        if ($row->flag1)
                            $attribs .= ' type="password"';
                        else
                            $attribs .= ' type="text"';
                        switch ($row->flag2) {
                            case 1:
                                $attribs .= ' disabled="disabled"';
                                break;
                            case 2:
                                $attribs .= ' readonly="readonly"';
                                break;
                            default:
                                break;
                        } // switch
                        $attribs .= $this->processor->script2clause($row);
                        echo indentc(2) . '<input id="ff_elem' . $row->id . '"' . $attribs . ' name="ff_nm_' . $row->name . '[]" value="' . $data1 . '"' . $class2 . '/>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Textarea':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        $styles = '';
                        switch ($row->flag2) {
                            case 1:
                                $attribs .= ' disabled="disabled"';
                                break;
                            case 2:
                                $attribs .= ' readonly="readonly"';
                                break;
                            default:
                                break;
                        } // switch
                        if ($row->width > 0) {
                            if ($row->widthmode > 0)
                                $styles .= 'width:' . $row->width . 'px;';
                            else
                                $attribs .= ' cols="' . $row->width . '"';
                        } // if
                        if ($row->height > 0) {
                            if ($row->heightmode > 0)
                                $styles .= 'height:' . $row->height . 'px;';
                            else {
                                $height = $row->height;
                                if ($height > 1 && stristr($this->processor->browser, 'mozilla'))
                                    $height--;
                                $attribs .= ' rows="' . $height . '"';
                            } // if
                        } // if
                        if ($styles != '')
                            $attribs .= ' style="' . $styles . '"';
                        $attribs .= $this->processor->script2clause($row);
                        echo indentc(2) . '<textarea id="ff_elem' . $row->id . '" name="ff_nm_' . $row->name . '[]"' . $attribs . $class2 . '>' . $data1 . '</textarea>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'File Upload':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->width > 0)
                            $attribs .= ' size="' . $row->width . '"';
                        if ($row->height > 0)
                            $attribs .= ' maxlength="' . $row->height . '"';
                        if ($row->flag2)
                            $attribs .= ' disabled="disabled"';
                        if ($row->data2 != '')
                            $attribs .= ' accept="' . $data2 . '"';
                        $attribs .= $this->processor->script2clause($row);
                        echo indentc(2) . '<input id="ff_elem' . $row->id . '"' . $attribs . ' type="file" name="ff_nm_' . $row->name . '[]"' . $class2 . '/>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Captcha':
                        $captcha_url = $this->captchaEndpointBuilder()->build(
                            Uri::root(true),
                            $this->processor->app->isClient('administrator'),
                            (int) $this->processor->form
                        )['captcha'];
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();
                        $attribs = '';
                        if ($row->width > 0)
                            $attribs .= 'width:' . $row->width . 'px;';
                        if ($row->height > 0)
                            $attribs .= 'height:' . $row->height . 'px;';
                        echo '<img id="ff_capimgValue" class="ff_capimg" src="' . $captcha_url . '"/>';
                        echo '<br/>';
                        echo '<input type="text" style="' . $attribs . '" name="bfCaptchaEntry" id="bfCaptchaEntry" />';
                        //echo '<br/>';
                        echo '<a href="#" onclick="document.getElementById(\'bfCaptchaEntry\').value=\'\';document.getElementById(\'bfCaptchaEntry\').focus();document.getElementById(\'ff_capimgValue\').src = \'' . $captcha_url . '&bfMathRandom=\' + Math.random(); return false"><img src="' . Uri::root() . 'media/com_breezingformsng/images/site/captcha/refresh-captcha.png" border="0" /></a>';
                        echo indentc(1) . '</div>' . nl();
                        break;
                    case 'Query List':
                        echo indentc(1) . '<div id="ff_div' . $row->id . '" style="' . $attribs . '"' . $class1 . '>' . nlc();

                        // unpack settings
                        $settings = explode("\n", $row->data1);
                        $scnt = count($settings);
                        for ($s = 0; $s < $scnt; $s++)
                            $this->processor->trim($settings[$s]);
                        $trhclass = '';
                        $tr1class = '';
                        $tr2class = '';
                        $trfclass = '';
                        $tdfclass = '';
                        $pagenav = 1;
                        $attribs = '';
                        if ($scnt > 0 && $settings[0] != '')
                            $attribs .= ' border="' . $settings[0] . '"';
                        if ($scnt > 1 && $settings[1] != '')
                            $attribs .= ' cellspacing="' . $settings[1] . '"';
                        if ($scnt > 2 && $settings[2] != '')
                            $attribs .= ' cellpadding="' . $settings[2] . '"';
                        if ($scnt > 3 && $settings[3] != '')
                            $trhclass = ' class="' . $this->processor->getClassName($settings[3]) . '"';
                        if ($scnt > 4 && $settings[4] != '')
                            $tr1class = ' class="' . $this->processor->getClassName($settings[4]) . '"';
                        if ($scnt > 5 && $settings[5] != '')
                            $tr2class = ' class="' . $this->processor->getClassName($settings[5]) . '"';
                        if ($scnt > 6 && $settings[6] != '')
                            $trfclass = ' class="' . $this->processor->getClassName($settings[6]) . '"';
                        if ($scnt > 7 && $settings[7] != '')
                            $tdfclass = ' class="' . $this->processor->getClassName($settings[7]) . '"';
                        if ($scnt > 8 && $settings[8] != '')
                            $pagenav = $settings[8];

                        if ($row->width > 0)
                            $attribs .= ' width="100%"';

                        // display 1st page of table
                        echo indentc(2) . '<table id="ff_elem' . $row->id . '"' . $attribs . $class2 . '>' . nl();

                        $cols = &$this->processor->queryCols['ff_' . $row->id];
                        $colcnt = count($cols);

                        // display header
                        if ($row->flag1) {
                            echo indentc(3) . '<tr' . $trhclass . '>' . nlc();
                            $skip = 0;
                            for ($c = 0; $c < $colcnt; $c++)
                                if ($skip > 0)
                                    $skip--;
                                else {
                                    $col = &$cols[$c];
                                    if ($col->thspan > 0) {
                                        $attribs = '';
                                        $style = '';
                                        switch ($col->thalign) {
                                            case 1:
                                                $style .= 'text-align:left;';
                                                break;
                                            case 2:
                                                $style .= 'text-align:center;';
                                                break;
                                            case 3:
                                                $style .= 'text-align:right;';
                                                break;
                                            case 4:
                                                $style .= 'text-align:justify;';
                                                break;
                                            default:
                                                ;
                                        } // switch
                                        switch ($col->thvalign) {
                                            case 1:
                                                $attribs .= ' valign="top"';
                                                break;
                                            case 2:
                                                $attribs .= ' valign="middle"';
                                                break;
                                            case 3:
                                                $attribs .= ' valign="bottom"';
                                                break;
                                            case 4:
                                                $attribs .= ' valign="baseline"';
                                                break;
                                            default:
                                                ;
                                        } // switch
                                        if ($col->thwrap == 1)
                                            $attribs .= ' nowrap="nowrap"';
                                        if ($col->thspan > 1) {
                                            $attribs .= ' colspan="' . $col->thspan . '"';
                                            $skip = $col->thspan - 1;
                                        } // if
                                        if ($col->class1 != '')
                                            $attribs .= ' class="' . $this->processor->getClassName($col->class1) . '"';
                                        if (intval($col->width) > 0 && !$skip) {
                                            $style .= 'width:' . $col->width;
                                            if ($col->widthmd)
                                                $style .= '%;';
                                            else
                                                $style .= 'px;';
                                        } // if
                                        if ($style != '')
                                            $attribs .= ' style="' . $style . '"';
                                        if ($c == 0 && $row->flag2 > 0) {
                                            if ($row->flag2 == 1)
                                                echo indentc(4) . '<th' . $attribs . '><input type="checkbox" id="ff_cb' . $row->id . '" onclick="ff_selectAllQueryRows(' . $row->id . ',this.checked);" /></th>' . nlc();
                                            else
                                                echo indentc(4) . '<th' . $attribs . '></th>' . nlc();
                                        } else
                                            echo indentc(4) . '<th' . $attribs . '>' . $this->processor->replaceCode($col->title, Text::_('COM_BREEZINGFORMSNG_PROCESS_QTITLEOF') . " $row->name::$col->name", 'e', $row->id, 2) . '</th>' . nlc();
                                    } // if
                                    unset($col);
                                } // if
                            echo indentc(3) . '</tr>' . nl();
                        } // if
                        // display data rows
                        $qrows = &$this->processor->queryRows['ff_' . $row->id];
                        $qcnt = count($qrows);
                        $k = 1;
                        if ($row->height > 0 && $qcnt > $row->height)
                            $qcnt = $row->height;
                        for ($q = 0; $q < $qcnt; $q++) {
                            $qrow = &$qrows[$q];
                            if ($k == 1)
                                $cl = $tr1class;
                            else
                                $cl = $tr2class;
                            echo indentc(3) . '<tr' . $cl . '>' . nlc();
                            $skip = 0;
                            for ($c = 0; $c < $colcnt; $c++) {
                                $col = &$cols[$c];
                                if ($col->thspan > 0) {
                                    $attribs = '';
                                    $style = '';
                                    switch ($col->align) {
                                        case 1:
                                            $style .= 'text-align:left;';
                                            break;
                                        case 2:
                                            $style .= 'text-align:center;';
                                            break;
                                        case 3:
                                            $style .= 'text-align:right;';
                                            break;
                                        case 4:
                                            $style .= 'text-align:justify;';
                                            break;
                                        default:
                                            ;
                                    } // switch
                                    switch ($col->valign) {
                                        case 1:
                                            $attribs .= ' valign="top"';
                                            break;
                                        case 2:
                                            $attribs .= ' valign="middle"';
                                            break;
                                        case 3:
                                            $attribs .= ' valign="bottom"';
                                            break;
                                        case 4:
                                            $attribs .= ' valign="baseline"';
                                            break;
                                        default:
                                            ;
                                    } // switch
                                    if ($col->wrap == 1)
                                        $attribs .= ' nowrap="nowrap"';
                                    if ($k == 1)
                                        $cl = $col->class2;
                                    else
                                        $cl = $col->class3;
                                    if ($cl != '')
                                        $attribs .= ' class="' . $this->processor->getClassName($cl) . '"';
                                    if (!$skip && $col->thspan > 1)
                                        $skip = $col->thspan;
                                    if ($skip && $q == 0)
                                        if (intval($col->width) > 0) {
                                            $style .= 'width:' . $col->width;
                                            if ($col->widthmd)
                                                $style .= '%;';
                                            else
                                                $style .= 'px;';
                                        } // if
                                    if ($skip > 0)
                                        $skip--;
                                    if ($style != '')
                                        $attribs .= ' style="' . $style . '"';
                                    if ($c == 0 && $row->flag2 > 0) {
                                        if ($row->flag2 == 1)
                                            echo indentc(4) . '<td' . $attribs . '><input type="checkbox" id="ff_cb' . $row->id . '_' . $q . '" value="' . $qrow[$c] . '"  name="ff_nm_' . $row->name . '[]"/></td>' . nlc();
                                        else
                                            echo indentc(4) . '<td' . $attribs . '><input type="radio" id="ff_cb' . $row->id . '_' . $q . '" value="' . $qrow[$c] . '"  name="ff_nm_' . $row->name . '[]"/></td>' . nlc();
                                    } else
                                        echo indentc(4) . '<td' . $attribs . '>' . $qrow[$c] . '</td>' . nlc();
                                } // if
                                unset($col);
                                if ($this->processor->dying)
                                    break;
                            } // for
                            echo indentc(3) . '</tr>' . nl();
                            $k = 3 - $k;
                            unset($qrow);
                            if ($this->processor->dying)
                                break;
                        } // for
                        if ($this->processor->bury()) {
                            $this->abortViewRendering();
                            return;
                        }

                        // display footer
                        if ($row->height > 0 && $pagenav > 0) {
                            $span = 0;
                            for ($c = 0; $c < $colcnt; $c++)
                                if ($cols[$c]->thspan > 0)
                                    $span++;
                            $pages = intval((count($qrows) + $row->height - 1) / $row->height);
                            echo indentc(3) . '<tr' . $trfclass . '>' . nlc();
                            echo indentc(4) . '<td colspan="' . $span . '"' . $tdfclass . '>' . nlc();
                            if ($pages > 1) {
                                echo indentc(5);
                                if ($pagenav <= 4)
                                    echo '&lt;&lt; ';
                                if ($pagenav <= 2)
                                    echo Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGESTART') . ' ';
                                if ($pagenav <= 4)
                                    echo '&lt; ';
                                if ($pagenav <= 2)
                                    echo Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGEPREV') . ' ';
                                echo nlc();
                                if ($pagenav % 2) {
                                    echo indentc(5);
                                    echo '1 ';
                                    for ($p = 2; $p <= $pages; $p++)
                                        echo indentc(5) . '<a href="javascript:ff_dispQueryPage(' . $row->id . ',' . $p . ');">' . $p . '</a> ' . nlc();
                                    echo nlc();
                                } // if
                                if ($pagenav <= 4) {
                                    echo indentc(5) . '<a href="javascript:ff_dispQueryPage(' . $row->id . ',2);">';
                                    if ($pagenav <= 2)
                                        echo Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGENEXT') . ' ';
                                    echo '&gt;</a> ' . nlc();
                                    echo indentc(5) . '<a href="javascript:ff_dispQueryPage(' . $row->id . ',' . $pages . ');">';
                                    if ($pagenav <= 2)
                                        echo Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGEEND') . ' ';
                                    echo '&gt;&gt;</a>' . nlc();
                                } // if
                            } // if
                            echo indentc(4) . '</td>' . nlc();
                            echo indentc(3) . '</tr>' . nl();
                        } // if
                        // table end
                        echo indentc(2) . '</table>' . nlc();
                        echo indentc(1) . '</div>' . nl();
                        unset($qrows);
                        unset($cols);
                        break;
                    default:
                        break;
                } // switch
                unset($row);
            } // for
        } else if (trim($this->processor->formrow->template_code_processed) == 'QuickMode') {

            if ($this->processor->isMobile) {

                // nothing
            } else {
                //if(true){

                $quickMode = $this->createQuickModeRenderer($rootMdata);
                $this->processor->quickmode = $quickMode;
            }

            if ($is_mobile_type == 'choose') {
                $this->renderMobileChoice();
            }

            $quickMode->render();
        }

        if ($this->processor->editable) {
            echo $this->postRenderScriptBuilder()->build('bfLoadEditable');
        }

        if ($cbRecord !== null) {
            echo $this->postRenderScriptBuilder()->build('bfLoadContentBuilderEditable');
        }

        if ($cbForm !== null && count($cbNonEditableFields)) {
            echo $this->postRenderScriptBuilder()->build('bfDisableContentBuilderFields');
        }

        // CONTENTBUILDER
        // writing hidden input for groups. helps on recording updates, otherwise no value would be transferred.
        // the "cbGroupMark" won't be stored.
        if ($cbForm !== null) {
            for ($i = 0; $i < $this->processor->rowcount; $i++) {
                $row = $this->processor->rows[$i];
                switch ($row->type) {
                    case 'Checkbox':
                    case 'Checkbox Group':
                    case 'Radio Button':
                    case 'Radio Group':
                    case 'Select List':
                        // temporary removed until further clarification if needed or not as this will interfere with javasripts on group elements (loosing their type)
                        //echo '<input type="hidden" name="ff_nm_' . $row->name . '[]" value="cbGroupMark"/>' . nl();
                        break;
                }
            }
        }

        if ($this->paymentProviderDetector()->hasSupportedProvider($this->processor->rows)) {
            echo $this->paymentMethodFieldBuilder()->build(indentc(1));
        }

        switch ($this->processor->runmode) {
            case _FF_RUNMODE_FRONTEND:
                echo $this->formContextFieldsBuilder()->build([
                    'ff_contentid' => $this->processor->app->getInput()->getInt('ff_contentid', 0),
                    'ff_applic' => $this->processor->app->getInput()->getWord('ff_applic', ''),
                    'ff_record_id' => $this->processor->record_id,
                    'ff_module_id' => $this->processor->app->getInput()->getInt('ff_module_id', 0),
                ], indentc(1));
                echo indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities((string) $this->processor->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_task" value="submit"/>' . nl() .
                    $this->formTokenFieldBuilder()->build(
                        \Joomla\CMS\HTML\HTMLHelper::_('form.token'),
                        indentc(1),
                        nl()
                    );
                echo $this->formOptionalContextFieldsBuilder()->build(
                    $this->processor->target,
                    (bool) $this->processor->inframe,
                    (bool) $this->processor->border,
                    $this->processor->page,
                    $this->processor->align,
                    $this->processor->top,
                    indentc(1),
                    true,
                    true,
                    true,
                    true,
                    nl()
                );
                echo $this->additionalHiddenFieldsBuilder()->build($ff_otherparams, indentc(1), nl());
                if ($this->processor->app->getInput()->getInt('cb_form_id', 0)) {
                    echo $this->contentBuilderTechnicalFieldsBuilder()->build(
                        '',
                        $this->processor->app->getInput()->getInt('cb_form_id', 0),
                        $this->processor->app->getInput()->getInt('cb_record_id', 0),
                        $this->processor->app->getInput()->getBool('cbIsNew', false)
                    );
                }
                echo $this->formRoutingFieldsBuilder()->build(
                    $this->processor->app->getInput()->getString('return', ''),
                    $this->processor->app->getInput()->getString('tmpl', ''),
                    nl()
                );
                echo '</form>' . nl();
                break;

            case _FF_RUNMODE_BACKEND:
                echo indentc(1) . '<input type="hidden" name="option" value="com_breezingformsng"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="act" value="run"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities((string) $this->processor->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                    indentc(1) . '<input type="hidden" name="ff_task" value="submit"/>' . nl() .
                    $this->formTokenFieldBuilder()->build(
                        \Joomla\CMS\HTML\HTMLHelper::_('form.token'),
                        indentc(1),
                        nl()
                    ) .
                    $this->formContextFieldsBuilder()->build([
                        'ff_contentid' => $this->processor->app->getInput()->getInt('ff_contentid', 0),
                        'ff_applic' => $this->processor->app->getInput()->getWord('ff_applic', ''),
                        'ff_record_id' => $this->processor->record_id,
                        'ff_module_id' => $this->processor->app->getInput()->getInt('ff_module_id', 0),
                        'ff_runmode' => $this->processor->runmode,
                    ], indentc(1));
                echo $this->formOptionalContextFieldsBuilder()->build(
                    $this->processor->target,
                    (bool) $this->processor->inframe,
                    (bool) $this->processor->border,
                    $this->processor->page,
                    $this->processor->align,
                    $this->processor->top,
                    indentc(1),
                    true,
                    true,
                    true,
                    true,
                    nl()
                );
                if ($this->processor->app->getInput()->getInt('cb_form_id', 0)) {
                    echo $this->contentBuilderTechnicalFieldsBuilder()->build(
                        '',
                        $this->processor->app->getInput()->getInt('cb_form_id', 0),
                        $this->processor->app->getInput()->getInt('cb_record_id', 0),
                        $this->processor->app->getInput()->getBool('cbIsNew', false)
                    );
                }
                echo $this->formRoutingFieldsBuilder()->build(
                    $this->processor->app->getInput()->getString('return', ''),
                    $this->processor->app->getInput()->getString('tmpl', ''),
                    nl()
                );
                echo '</form>' . nl();
                break;

            default: // _FF_RUNMODE_PREVIEW:
                if ($this->processor->inframe) {
                    echo indentc(1) . '<input type="hidden" name="option" value="com_breezingformsng"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_frame" value="1"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities((string) $this->processor->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_task" value="submit"/>' . nl() .
                    $this->formTokenFieldBuilder()->build(
                        \Joomla\CMS\HTML\HTMLHelper::_('form.token'),
                        indentc(1),
                        nl()
                    ) .
                    $this->formContextFieldsBuilder()->build([
                        'ff_contentid' => $this->processor->app->getInput()->getInt('ff_contentid', 0),
                        'ff_applic' => $this->processor->app->getInput()->getWord('ff_applic', ''),
                        'ff_record_id' => $this->processor->record_id,
                        'ff_module_id' => $this->processor->app->getInput()->getInt('ff_module_id', 0),
                        'ff_runmode' => $this->processor->runmode,
                    ], indentc(1));
                    echo $this->formOptionalContextFieldsBuilder()->build(
                        $this->processor->target,
                        (bool) $this->processor->inframe,
                        (bool) $this->processor->border,
                        $this->processor->page,
                        $this->processor->align,
                        $this->processor->top,
                        indentc(1),
                        false,
                        true,
                        false,
                        false,
                        nl()
                    );
                    if ($this->processor->app->getInput()->getInt('cb_form_id', 0)) {
                        echo $this->contentBuilderTechnicalFieldsBuilder()->build(
                            '',
                            $this->processor->app->getInput()->getInt('cb_form_id', 0),
                            $this->processor->app->getInput()->getInt('cb_record_id', 0),
                            $this->processor->app->getInput()->getBool('cbIsNew', false)
                        );
                    }
                    echo $this->formRoutingFieldsBuilder()->build(
                        $this->processor->app->getInput()->getString('return', ''),
                        $this->processor->app->getInput()->getString('tmpl', ''),
                        nl()
                    );
                    echo '</form>' . nl();
                } // if
        } // if
        if ($this->executeAfterFormPiece()) {
            $this->abortViewRendering();
            return;
        }

        $this->closeFormRendering();
        $this->finishViewRendering();

    }

    /**
     * Create the renderer selected by the QuickMode theme settings.
     *
     * @param array<string, mixed> $rootMdata
     */
    private function createQuickModeRenderer(array $rootMdata): object
    {
        if (isset($rootMdata['themebootstrapThemeEngine']) && $rootMdata['themebootstrapThemeEngine'] == 'bootstrap') {
            if (isset($rootMdata['themebootstrapMode']) && $rootMdata['themebootstrapMode']) {
                return new OnePageRenderer($this->processor);
            }

            return new BootstrapRenderer($this->processor);
        }

        return new ClassicRenderer($this->processor);
    }

    /**
     * Create and configure the renderer used for a mobile QuickMode request.
     *
     * @param array<string, mixed> $rootMdata
     */
    private function createMobileRenderer(array $rootMdata): MobileRenderer
    {
        $quickMode = new MobileRenderer($this->processor);

        if (isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && $rootMdata['forceMobile']) {
            $quickMode->forceMobileUrl = isset($rootMdata['forceMobileUrl']) ? $rootMdata['forceMobileUrl'] : 'index.php';
        }

        return $quickMode;
    }

    private function renderMobileChoice(): void
    {
        $currentUrl = Uri::getInstance()->toString();
        $returnUrl = $currentUrl;
        $returnUrl = (strstr($returnUrl, '?non_mobile=1') !== false ? str_replace('?non_mobile=1', '', $returnUrl) : str_replace('&non_mobile=1', '', $returnUrl));
        $returnUrl = $returnUrl . (strstr($returnUrl, '?') !== false ? '&' : '?') . 'mobile=1';
        echo '<script type="text/javascript">
                <!--
                var bf_mobile_url = ' . json_encode($returnUrl) . ';
                //-->
                </script>';
        echo '<div style="display: block; text-align: center;"><button class="ff_elem btn btn-primary" onclick="location.href=bf_mobile_url;"><span>' . Text::_('COM_BREEZINGFORMSNG_MOBILE_VERSION') . '</span></button></div><div></div>';
    }

    private function syncMobileSessionPreference(): void
    {
        if ($this->processor->app->getInput()->getBool('non_mobile', false)) {
            $this->processor->app->getSession()->clear('com_breezingformsng.mobile');
        } elseif ($this->processor->app->getInput()->getBool('mobile', false)) {
            $this->processor->app->getSession()->set('com_breezingformsng.mobile', true);
        }
    }

    /**
     * Apply the mobile mode selected by the request and template settings.
     *
     * @param array<string, mixed> $rootMdata
     */
    private function applyMobileMode(array $rootMdata): bool
    {
        if ($this->processor->app->getInput()->getString('ff_applic', '') != 'mod_facileforms' && $this->processor->app->getInput()->getInt('ff_frame', 0) != 1 && bf_is_mobile()) {
            $this->processor->isMobile = isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && $rootMdata['forceMobile'] ? true : (isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && $this->processor->app->getSession()->get('com_breezingformsng.mobile', false) ? true : false);

            return true;
        }

        $this->processor->isMobile = false;

        if (isset($rootMdata['themebootstrapThemeEngine']) && $rootMdata['themebootstrapThemeEngine'] == 'bootstrap') {
            $this->processor->legacy_wrap = false;
        }

        return false;
    }

    /**
     * Decode the QuickMode template metadata used by the rendering stages.
     *
     * @return array<string, mixed>
     */
    private function loadQuickModeMetadata(): array
    {
        $dataObject = json_decode(bf_b64dec($this->processor->formrow->template_code), true);

        return $dataObject['properties'];
    }

    /**
     * Determine whether the visitor should be offered the mobile version.
     *
     * @param array<string, mixed> $rootMdata
     */
    private function mobileChoiceType(bool $isDevice, array $rootMdata): string
    {
        if ($isDevice && isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && !$rootMdata['forceMobile']) {
            return 'choose';
        }

        return '';
    }

    /**
     * Build the script library and the registry used while linking callbacks.
     *
     * @return array{0: array<int|string, mixed>, 1: array<int|string, mixed>}
     */
    private function createScriptLibraryState(): array
    {
        $library = [];
        $this->processor->loadBuiltins($library);
        $this->processor->loadScripts($library);

        return [$library, []];
    }

    /**
     * Link the onload callback used when a form is displayed for the first time.
     *
     * @param array<int|string, mixed> $library
     * @param array<int|string, mixed> $linked
     */
    private function linkInitialOnload(array &$library, array &$linked): void
    {
        $code = "onload = function()" . nl() .
            "{" . nl() .
            "    ff_initialize('formentry');" . nl() .
            "    ff_initialize('pageentry');" . nl();
        if ($this->processor->formrow->heightmode) {
            $code .= "    ff_resizepage(" . $this->processor->formrow->heightmode . ", " . $this->processor->formrow->height . ");" . nl();
        }
        if ($this->processor->showgrid) {
            $code .= "    ff_showgrid();" . nl();
        }
        $code .= "    if (ff_processor && ff_processor.traceBuffer) ff_traceWindow();" . nl() .
            "} // onload";
        $this->processor->linkcode('onload', $library, $linked, $code);
    }

    private function initializeFormRendering(): void
    {
        $this->processor->queryCols = [];
        $this->processor->queryRows = [];

        if (trim($this->processor->formrow->template_code_processed) == 'QuickMode' && $this->processor->legacy_wrap) {
            echo '<table style="display:none;width:100%;" id="bfReCaptchaWrap"><tr><td><div id="bfReCaptchaDiv"></div></td></tr></table>';
        }

        echo $this->formOpeningMarkupBuilder()->build(
            (string) $this->processor->form,
            $this->processor->formrow->class1 != ''
                ? $this->processor->getClassName($this->processor->formrow->class1)
                : '',
            (bool) $this->processor->legacy_wrap,
            nl()
        );

        $this->processor->status = $this->processor->app->getInput()->getCmd('ff_status', '');
        $this->processor->message = $this->processor->app->getInput()->getString('ff_message', '');
    }

    private function closeFormRendering(): void
    {
        echo $this->formClosingMarkupBuilder()->build((bool) $this->processor->legacy_wrap, nl());
    }

    private function abortViewRendering(): void
    {
        ob_end_flush();
        restore_error_handler();
    }

    private function finishViewRendering(): void
    {
        if ($this->processor->traceMode & _FF_TRACEMODE_DIRECT) {
            $this->processor->dumpTrace();
            ob_end_flush();
            echo '</pre>';
        } else {
            ob_end_flush();
            $this->processor->dumpTrace();
        }

        restore_error_handler();
    }

    private function executeBeforeFormPiece(): bool
    {
        switch ($this->processor->formrow->piece1cond) {
            case 1:
                $piece1id = (int) $this->processor->formrow->piece1id;
                $query = $this->processor->database->getQuery(true)
                    ->select(['name', 'code'])
                    ->from($this->processor->database->quoteName('#__facileforms_pieces'))
                    ->where($this->processor->database->quoteName('id') . ' = :piece1id')
                    ->where($this->processor->database->quoteName('published') . ' = 1')
                    ->bind(':piece1id', $piece1id, ParameterType::INTEGER);
                $this->processor->database->setQuery($query);
                $rows = $this->processor->database->loadObjectList();
                if (count($rows)) {
                    echo $this->processor->execPiece($rows[0]->code, Text::_('COM_BREEZINGFORMSNG_PROCESS_BFPIECE') . ' ' . $rows[0]->name, 'p', $this->processor->formrow->piece1id, null);
                }
                break;
            case 2:
                echo $this->processor->execPiece($this->processor->formrow->piece1code, Text::_('COM_BREEZINGFORMSNG_PROCESS_BFPIECEC'), 'f', $this->processor->form, 2);
                break;
            default:
                break;
        }

        return $this->processor->bury();
    }

    private function executeAfterFormPiece(): bool
    {
        switch ($this->processor->formrow->piece2cond) {
            case 1:
                $piece2id = (int) $this->processor->formrow->piece2id;
                $query = $this->processor->database->getQuery(true)
                    ->select(['name', 'code'])
                    ->from($this->processor->database->quoteName('#__facileforms_pieces'))
                    ->where($this->processor->database->quoteName('id') . ' = :piece2id')
                    ->where($this->processor->database->quoteName('published') . ' = 1')
                    ->bind(':piece2id', $piece2id, ParameterType::INTEGER);
                $this->processor->database->setQuery($query);
                $rows = $this->processor->database->loadObjectList();
                if (count($rows)) {
                    echo $this->processor->execPiece(
                        $rows[0]->code,
                        Text::_('COM_BREEZINGFORMSNG_PROCESS_AFPIECE') . ' ' . $rows[0]->name,
                        'p',
                        $this->processor->formrow->piece2id,
                        null
                    );
                }
                break;
            case 2:
                echo $this->processor->execPiece(
                    $this->processor->formrow->piece2code,
                    Text::_('COM_BREEZINGFORMSNG_PROCESS_AFPIECEC'),
                    'f',
                    $this->processor->form,
                    2
                );
                break;
            default:
                break;
        }

        return $this->processor->bury();
    }

    /**
     * Link the onload callback used after a form submission.
     *
     * @param array<int|string, mixed> $library
     * @param array<int|string, mixed> $linked
     */
    private function linkSubmittedOnload(array &$library, array &$linked): void
    {
        $functionName = '';

        switch ($this->processor->formrow->script2cond) {
            case 1:
                $script2id = (int) $this->processor->formrow->script2id;
                $query = $this->processor->database->getQuery(true)
                    ->select('name')
                    ->from($this->processor->database->quoteName('#__facileforms_scripts'))
                    ->where($this->processor->database->quoteName('id') . ' = :script2id')
                    ->where($this->processor->database->quoteName('published') . ' = 1')
                    ->bind(':script2id', $script2id, ParameterType::INTEGER);
                $this->processor->database->setQuery($query);
                $functionName = $this->processor->database->loadResult();
                break;
            case 2:
                $functionName = 'ff_' . $this->processor->formrow->name . '_submitted';
                break;
            default:
                break;
        }

        if ($functionName == '' && !$this->processor->formrow->heightmode && !$this->processor->showgrid) {
            return;
        }

        $code = "onload = function()" . nl() .
            "{" . nl();
        if ($this->processor->formrow->heightmode) {
            $code .= "    ff_resizepage(" . $this->processor->formrow->heightmode . ", " . $this->processor->formrow->height . ");" . nl();
        }
        if ($this->processor->showgrid) {
            $code .= "    ff_showgrid();" . nl();
        }
        if ($functionName != '') {
            $jsonReturn = json_encode($this->processor->message, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
            if (trim($jsonReturn) === '') {
                $jsonReturn = '""';
            }
            $code .= "    " . $functionName . "(" . $this->processor->status . "," . $jsonReturn . ");" . nl();
        }
        $code .= '} // onload';
        $this->processor->linkcode('onload', $library, $linked, $code);
    }

    /**
     * Build the client-side file extension validator.
     *
     * @return array{0: string, 1: int}
     */
    private function buildFileExtensionsCheck(): array
    {
        $cntFiles = 0;
        $fileExtensionError = json_encode(
            Text::_('COM_BREEZINGFORMSNG_FILE_EXTENSION_NOT_ALLOWED'),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
        $fileExtensionsCheck = 'function checkFileExtensions(){';
        for ($i = 0; $i < $this->processor->rowcount; $i++) {
            $row = $this->processor->rows[$i];
            if ($row->type == 'File Upload' && trim($this->processor->formrow->template_code) != '') {
                if (trim($row->data2) != '') {
                    $exts = explode(',', $row->data2);
                    $extsCount = count($exts);
                    $fileExtensionsCheck .= 'var ff_elem' . $row->id . 'Exts = false;';
                    for ($x = 0; $x < $extsCount; $x++) {
                        $fileExtensionsCheck .= '
							if(!ff_elem' . $row->id . 'Exts && document.getElementById("ff_elem' . $row->id . '").value.toLowerCase().lastIndexOf(".' . strtolower(trim($exts[$x])) . '") != -1){
								ff_elem' . $row->id . 'Exts = true;
							}else if(!ff_elem' . $row->id . 'Exts && document.getElementById("ff_elem' . $row->id . '").value == ""){
								ff_elem' . $row->id . 'Exts = true;
							}';
                    }
                    $fileExtensionsCheck .= '
					if(!ff_elem' . $row->id . 'Exts){
						if(typeof bfUseErrorAlerts == "undefined"){
							alert(' . $fileExtensionError . ');
						} else {
							bfShowErrors(' . $fileExtensionError . ');
						}
						if(ff_currentpage != ' . $row->page . ')ff_switchpage(' . $row->page . ');
                                                if(document.getElementById("bfSubmitButton")){
                                                    document.getElementById("bfSubmitButton").disabled = false;
                                                }
                                                if(typeof JQuery != "undefined"){JQuery(".bfCustomSubmitButton").prop("disabled", false);}
						return false;
					}
					';
                    $cntFiles++;
                }
            }
        }
        $fileExtensionsCheck .= '
			return true;
		}
		';

        return [$fileExtensionsCheck, $cntFiles];
    }

    /**
     * Prepare the client-side state for one classic Query List element.
     */
    private function prepareQueryListRow(object $row, int &$queryCheckboxCount, string &$queryCode): void
    {
        if ($row->flag2) {
            $queryCheckboxCount++;
        }

        $key = 'ff_' . $row->id;
        $this->processor->queryCols[$key] = [];
        $columns = &$this->processor->queryCols[$key];

        if ($this->processor->trim($row->data3)) {
            foreach (explode("\n", $row->data3) as $definition) {
                if ($definition === '') {
                    continue;
                }

                $column = new QueryColumn();
                $column->unpack($definition);
                $this->processor->compileQueryCol($row, $column);
                $columns[] = $column;
            }
        }

        $checkbox = $row->flag2 ?: 0;
        $header = $row->flag1 ? 1 : 0;
        $pageNavigation = 1;
        $settings = explode("\n", $row->data1);

        if (count($settings) > 8 && $this->processor->trim($settings[8])) {
            $pageNavigation = $settings[8];
        }

        $queryCode .= nl() .
            'ff_queryCurrPage[' . $row->id . '] = 1;' . nl() .
            'ff_queryPageSize[' . $row->id . '] = ' . $row->height . ';' . nl() .
            'ff_queryCheckbox[' . $row->id . '] = ' . $checkbox . ';' . nl() .
            'ff_queryHeader[' . $row->id . '] = ' . $header . ';' . nl() .
            'ff_queryPagenav[' . $row->id . '] = ' . $pageNavigation . ';' . nl() .
            'ff_queryCols[' . $row->id . '] = [';

        foreach ($columns as $index => $column) {
            $queryCode .= $column->thspan > 0 ? '1' : '0';
            if ($index < count($columns) - 1) {
                $queryCode .= ',';
            }
        }

        $queryCode .= '];' . nl();
        $this->processor->queryRows[$key] = [];
        $this->processor->execQuery($row, $this->processor->queryRows[$key], $columns);
        $queryCode .= 'ff_queryRows[' . $row->id . '] = ' .
            $this->processor->expJsValue($this->processor->queryRows[$key]) . ';' . nl();

        unset($columns);
    }

    /**
     * Link the icon border hover callbacks used when the form has at least
     * one "Icon" row. Returns whether view() should bury/return - mirrors
     * the original inline control flow exactly: the second linkcode() call
     * never runs if bury() is already true after the first.
     *
     * @param array<int|string, mixed> $library
     * @param array<int|string, mixed> $linked
     */
    private function registerIconBorderScripts(array &$library, array &$linked): bool
    {
        $this->processor->linkcode(
            'ff_hideIconBorder',
            $library,
            $linked,
            'function ff_hideIconBorder(element)' . nl() .
            '{' . nl() .
            '    element.style.border = "none";' . nl() .
            '} // ff_hideIconBorder'
        );

        if ($this->processor->bury()) {
            return true;
        }

        $this->processor->linkcode(
            'ff_dispIconBorder',
            $library,
            $linked,
            'function ff_dispIconBorder(element)' . nl() .
            '{' . nl() .
            '    element.style.border = "1px outset";' . nl() .
            '} // ff_dispIconBorder'
        );

        return $this->processor->bury();
    }

    /**
     * Register the three callbacks associated with one classic element.
     *
     * The callback order and early-return behavior are part of the historical
     * rendering contract. The validation callback also closes the active
     * output buffer when it triggers bury().
     *
     * @param object $row Classic element row.
     * @param array<int|string, mixed> $library
     * @param array<int|string, mixed> $linked
     */
    private function registerElementCallbacks(object $row, array &$library, array &$linked): bool
    {
        $this->processor->addFunction(
            $row->script1cond,
            $row->script1id,
            'ff_' . $row->name . '_init',
            $row->script1code,
            $library,
            $linked,
            'e',
            $row->id,
            1
        );
        if ($this->processor->bury()) {
            return true;
        }

        $this->processor->addFunction(
            $row->script2cond,
            $row->script2id,
            'ff_' . $row->name . '_action',
            $row->script2code,
            $library,
            $linked,
            'e',
            $row->id,
            1
        );
        if ($this->processor->bury()) {
            return true;
        }

        $this->processor->addFunction(
            $row->script3cond,
            $row->script3id,
            'ff_' . $row->name . '_validate',
            $row->script3code,
            $library,
            $linked,
            'e',
            $row->id,
            1
        );
        if ($this->processor->bury()) {
            ob_end_clean();
            return true;
        }

        return false;
    }

    /**
     * Count the element types that require post-render assets or behavior.
     */
    private function collectElementMetadata(object $row, int &$icons, int &$tooltips): void
    {
        if ($row->type == "Icon") {
            $icons++;
        }

        if ($row->type == "Tooltip") {
            $tooltips++;
        }
    }

    /**
     * Register the scan-only callback used by static HTML elements.
     *
     * @param array<int|string, mixed> $library
     * @param array<int|string, mixed> $linked
     */
    private function registerStaticTextScanCallback(object $row, array &$library, array &$linked): void
    {
        if ($row->type == 'Static Text/HTML') {
            $this->processor->linkcode('#scanonly', $library, $linked, $row->data1);
        }
    }

    /**
     * Create the default CAPTCHA error payload and submit callback.
     *
     * @return array{0: string, 1: string}
     */
    private function createCaptchaDefaults(): array
    {
        $captchaError = json_encode(
            Text::_('COM_BREEZINGFORMSNG_CAPTCHA_MISSING_WRONG'),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        return [$captchaError, 'function bfCheckCaptcha(){if(checkFileExtensions())ff_submitForm2();}'];
    }

    /**
     * Build the legacy CAPTCHA/ReCaptcha submit-validation script for the
     * first Captcha or ReCaptcha row found on the form. Preserves the
     * original control flow verbatim: a "Captcha" row breaks out of the
     * loop immediately, while a "ReCaptcha" row does not - if a form has
     * multiple ReCaptcha rows, the last one silently wins. Returns
     * $capFunc unchanged (createCaptchaDefaults()'s default) when neither
     * row type is present.
     */
    private function buildCaptchaScript(string $captchaError, string $capFunc): string
    {
        $endpoints = $this->captchaEndpointBuilder()->build(
            Uri::root(true),
            $this->processor->app->isClient('administrator'),
            (int) $this->processor->form
        );

        $row = $this->captchaValidationRowSelector()->select(
            $this->processor->rows,
            $this->processor->rowcount
        );

        if ($row !== null) {
            if ($row->type == "Captcha") {
                $capFunc = '

				function bfAjaxObject101() {
					this.createRequestObject = function() {
						try {
							var ro = new XMLHttpRequest();
						}
						catch (e) {
							var ro = new ActiveXObject("Microsoft.XMLHTTP");
						}
						return ro;
					}
					this.sndReq = function(action, url, data) {
					
						if (action.toUpperCase() == "POST") {
							this.http.open(action,url,true);
							this.http.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							this.http.onreadystatechange = this.handleResponse;
							this.http.send(data);
						}
						else {
							this.http.open(action,url + "?" + data,true);
							this.http.onreadystatechange = this.handleResponse;
							this.http.send(null);
						}
					}
					this.handleResponse = function() {
						if ( me.http.readyState == 4) {
							if (typeof me.funcDone == "function") { me.funcDone();}
							var rawdata = me.http.responseText.split("|");
							for ( var i = 0; i < rawdata.length; i++ ) {
								var item = (rawdata[i]).split("=>");
								if (item[0] != "") {
									if (item[1].substr(0,3) == "%V%" ) {
										document.getElementById(item[0]).value = item[1].substring(3);
									}
									else {
										if(item[1] == "true"){
                                                                                    if(typeof bfDoFlashUpload != \'undefined\'){
                                                                                        bfDoFlashUpload();
                                                                                    } else {
									   		ff_submitForm2();
                                                                                    }
									   } else {
                                                                                if(typeof JQuery != "undefined" && JQuery("#bfSubmitMessage"))
									        {
                                                                                    JQuery("#bfSubmitMessage").css("visibility","hidden");
                                                                                    JQuery("#bfSubmitMessage").css("display","none");
									        }
                                                                                if(typeof bfUseErrorAlerts == "undefined"){
                                                                                    alert(' . $captchaError . ');
									        } else {
                                                                                   if(typeof inlineErrorElements != "undefined"){
                                                                                     inlineErrorElements.push(["bfCaptchaEntry",' . $captchaError . ']);
                                                                                   }
									           bfShowErrors(' . $captchaError . ');
									        }
                                                                                if(typeof ladda_button != "undefined"){
                                                                                    
                                                                                    bf_restore_submitbutton();
                                                                                }
                                                                                
                                                                                        document.getElementById(\'ff_capimgValue\').src = \'' . $endpoints['image'] . '\' + Math.random();
                                                                                        document.getElementById(\'bfCaptchaEntry\').value = "";
                                                                                        if(ff_currentpage != ' . $row->page . ')ff_switchpage(' . $row->page . ');
                                                                                        document.getElementById(\'bfCaptchaEntry\').focus();
                                                                                        if(document.getElementById("bfSubmitButton")){
                                                                                            document.getElementById("bfSubmitButton").disabled = false;
                                                                                        }
                                                                                        if(typeof JQuery != "undefined"){JQuery(".bfCustomSubmitButton").prop("disabled", false);}
										}
                                                                                
									}
								}
							}
						}
						if ((me.http.readyState == 1) && (typeof me.funcWait == "function")) { me.funcWait(); }
					}
					var me = this;
					this.http = this.createRequestObject();

					var funcWait = null;
					var funcDone = null;
				}

                                function bfCheckCaptcha(){
                                        if(checkFileExtensions()){
                                               var ao = new bfAjaxObject101();
                                               ao.sndReq("get","' . $endpoints['check'] . '"+document.getElementById("bfCaptchaEntry").value,"");
                                        }
                                }';
            } elseif ($row->type == "ReCaptcha") {

                $capFunc = 'var bfReCaptchaLoaded = true;
                                    function bfCheckCaptcha(){
					if(checkFileExtensions()){
                                                function bfValidateCaptcha()
                                                {
                                                    if(typeof bfInvisibleRecaptcha != "undefined" && bfInvisibleRecaptcha === false){
														if(typeof bfDoFlashUpload != \'undefined\'){
															bfDoFlashUpload();
														} else {
															ff_submitForm2();
														}
														return;
                                                    }
                                                    
                                                    if(typeof onloadBFNewRecaptchaCallback == "undefined"){
                                                        challengeField = JQuery("input#recaptcha_challenge_field").val();
                                                        responseField = JQuery("input#recaptcha_response_field").val();
                                                        var html = JQuery.ajax({
                                                        type: "POST",
                                                        url: "' . Route::_($endpoints['recaptcha']) . '",
                                                        data: "recaptcha_challenge_field=" + challengeField + "&recaptcha_response_field=" + responseField,
                                                        async: false
                                                        }).responseText;

                                                        if (html.replace(/^\s+|\s+$/, "") == "success")
                                                        {
                                                            if(typeof bfDoFlashUpload != \'undefined\'){
                                                                bfDoFlashUpload();
                                                            } else {
                                                                ff_submitForm2();
                                                            }
                                                        }
                                                        else
                                                        {
                                                                if(typeof bfUseErrorAlerts == "undefined"){
                                                                        alert(' . $captchaError . ');
                                                                } else {
                                                                    if(typeof inlineErrorElements != "undefined"){
                                                                        inlineErrorElements.push(["bfReCaptchaEntry",' . $captchaError . ']);
                                                                    }
                                                                    bfShowErrors(' . $captchaError . ');
                                                                }

                                                                if(ff_currentpage != ' . $row->page . ')ff_switchpage(' . $row->page . ');
                                                                Recaptcha.focus_response_field();

                                                                Recaptcha.reload();

                                                                if(document.getElementById("bfSubmitButton")){
                                                                    document.getElementById("bfSubmitButton").disabled = false;
                                                                }
                                                                if(typeof JQuery != "undefined"){JQuery(".bfCustomSubmitButton").prop("disabled", false);}
                                                                if(typeof ladda_button != "undefined"){
                                                                    bf_restore_submitbutton();
                                                                }
                                                                
                                                        }
                                                    }
                                                    else{
                                                        
                                                        if(typeof bfInvisibleRecaptcha != "undefined"){
                                                        
                                                            grecaptcha.execute();
                                                        }
                                                        
                                                        var gresponse = grecaptcha.getResponse();
                                                        
                                                        if(gresponse == ""){
                                                            
                                                            if(typeof bfInvisibleRecaptcha == "undefined"){
                                                            
	                                                            if(typeof bfUseErrorAlerts == "undefined"){
	                                                                    alert(' . $captchaError . ');
	                                                            } else {
	                                                                if(typeof inlineErrorElements != "undefined"){
	                                                                    inlineErrorElements.push(["bfReCaptchaEntry",' . $captchaError . ']);
	                                                                }
	                                                                bfShowErrors(' . $captchaError . ');
	                                                            }
                                                            
                                                            
                                                                if(ff_currentpage != ' . $row->page . ')ff_switchpage(' . $row->page . ');
                                                            }
                                                            if(document.getElementById("bfSubmitButton")){
                                                                document.getElementById("bfSubmitButton").disabled = false;
                                                            }
                                                            if(typeof JQuery != "undefined"){JQuery(".bfCustomSubmitButton").prop("disabled", false);}
                                                            if(typeof ladda_button != "undefined"){
                                                                bf_restore_submitbutton();
                                                            }
                                                            
                                                            
                                                        }else{
               
                                                            if(typeof bfDoFlashUpload != \'undefined\'){
                                                                bfDoFlashUpload();
                                                            } else {
                                                                ff_submitForm2();
                                                            }
                                                        }
                                                    }
                                                }

                                                bfValidateCaptcha();

					}
					}';
        }
        }

        return $capFunc;
    }

    /**
     * Link the form-level init and submission callbacks.
     *
     * @param array<int|string, mixed> $library
     * @param array<int|string, mixed> $linked
     */
    private function addFormScripts(array &$library, array &$linked): bool
    {
        $this->processor->addFunction(
            $this->processor->formrow->script1cond,
            $this->processor->formrow->script1id,
            'ff_' . $this->processor->formrow->name . '_init',
            $this->processor->formrow->script1code,
            $library,
            $linked,
            'f',
            $this->processor->form,
            1
        );
        if ($this->processor->bury()) {
            return true;
        }

        $this->processor->addFunction(
            $this->processor->formrow->script2cond,
            $this->processor->formrow->script2id,
            'ff_' . $this->processor->formrow->name . '_submitted',
            $this->processor->formrow->script2code,
            $library,
            $linked,
            'f',
            $this->processor->form,
            1
        );

        return $this->processor->bury();
    }

    // view

}
