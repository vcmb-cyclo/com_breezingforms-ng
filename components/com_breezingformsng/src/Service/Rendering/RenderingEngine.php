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
use Closure;
use Vcmb\Component\BreezingformsNG\Site\Table\QueryColumn;
use HTML_facileFormsProcessor;
use CB\Component\Contentbuilderng\Administrator\Helper\ContentbuilderngHelper;
use CB\Component\Contentbuilderng\Administrator\Helper\FormSourceFactory;
use CB\Component\Contentbuilderng\Administrator\Service\ArticleService;
use CB\Component\Contentbuilderng\Administrator\Service\ListSupportService;
use CB\Component\Contentbuilderng\Administrator\Service\PermissionService;
use CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\TokenizedDirectoryResolver;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\RuntimeAssetLoader;

/**
 * Page header, ContentBuilder path handling and form view rendering.
 */
final class RenderingEngine
{
    private ?TokenizedDirectoryResolver $tokenizedDirectoryResolverService = null;
    private ?ProcessorHeaderRenderer $processorHeaderRendererService = null;
    private ?ClassicStaticTextBuilder $classicStaticTextBuilderService = null;
    private ?ClassicHiddenInputBuilder $classicHiddenInputBuilderService = null;
    private ?ClassicTextInputBuilder $classicTextInputBuilderService = null;
    private ?ClassicTextareaBuilder $classicTextareaBuilderService = null;
    private ?ClassicChoiceBuilder $classicChoiceBuilderService = null;
    private ?ClassicSelectBuilder $classicSelectBuilderService = null;
    private ?ClassicRegularButtonBuilder $classicRegularButtonBuilderService = null;
    private ?ClassicGraphicButtonBuilder $classicGraphicButtonBuilderService = null;
    private ?ClassicFileUploadBuilder $classicFileUploadBuilderService = null;
    private ?ClassicCaptchaBuilder $classicCaptchaBuilderService = null;
    private ?ClassicQueryListSettingsBuilder $classicQueryListSettingsBuilderService = null;
    private ?ClassicQueryListHeaderBuilder $classicQueryListHeaderBuilderService = null;
    private ?ClassicQueryListCellBuilder $classicQueryListCellBuilderService = null;
    private ?ClassicQueryListRowBuilder $classicQueryListRowBuilderService = null;
    private ?ClassicQueryListFooterBuilder $classicQueryListFooterBuilderService = null;
    private ?ClassicQueryListMarkupBuilder $classicQueryListMarkupBuilderService = null;
    private ?ContentBuilderValueScriptBuilder $contentBuilderValueScriptBuilderService = null;
    private ?EditableRecordHydrationScriptBuilder $editableRecordHydrationScriptBuilderService = null;
    private ?HiddenFormFieldsBuilder $hiddenFormFieldsBuilderService = null;
    private ?LegacyScriptTagWrapperBuilder $legacyScriptTagWrapperBuilderService = null;
    private ?ContentBuilderReadonlyScriptBuilder $contentBuilderReadonlyScriptBuilderService = null;
    private ?EditableRecordLoader $editableRecordLoaderService = null;
    private ?PostRenderScriptBuilder $postRenderScriptBuilderService = null;
    private ?ContentBuilderTechnicalFieldsBuilder $contentBuilderTechnicalFieldsBuilderService = null;
    private ?FormClosingMarkupBuilder $formClosingMarkupBuilderService = null;
    private ?FormOpeningMarkupBuilder $formOpeningMarkupBuilderService = null;
    private ?FormOptionalContextFieldsBuilder $formOptionalContextFieldsBuilderService = null;
    private ?FormModeFinalizationBuilder $formModeFinalizationBuilderService = null;
    private ?QuickModeFormTagBuilder $quickModeFormTagBuilderService = null;
    private ?FileExtensionsCheckBuilder $fileExtensionsCheckBuilderService = null;
    private ?QueryListSelectAllScriptBuilder $queryListSelectAllScriptBuilderService = null;
    private ?QueryListNavigationBuilder $queryListNavigationBuilderService = null;
    private ?QueryListRowsRefreshBuilder $queryListRowsRefreshBuilderService = null;
    private ?QueryListPaginationTailBuilder $queryListPaginationTailBuilderService = null;
    private ?QueryListStateLibraryBuilder $queryListStateLibraryBuilderService = null;
    private ?QueryListRowStateBuilder $queryListRowStateBuilderService = null;
    private ?PaymentProviderDetector $paymentProviderDetectorService = null;
    private ?CaptchaSupportBuilder $captchaSupportBuilderService = null;
    private ?CaptchaValidationRowSelector $captchaValidationRowSelectorService = null;
    private ?CaptchaLegacyValidationScriptBuilder $captchaLegacyValidationScriptBuilderService = null;
    private ?CaptchaReCaptchaValidationScriptBuilder $captchaReCaptchaValidationScriptBuilderService = null;
    private ?CaptchaValidationScriptBuilder $captchaValidationScriptBuilderService = null;
    private ?ContentBuilderEditableRecordScriptBuilder $contentBuilderEditableRecordScriptBuilderService = null;
    private ?ContentBuilderNonEditableFieldsResolver $contentBuilderNonEditableFieldsResolverService = null;
    private ?ContentBuilderFormAssociationLoader $contentBuilderFormAssociationLoaderService = null;
    private ?ContentBuilderFormDataLoader $contentBuilderFormDataLoaderService = null;
    private ?ContentBuilderPermissionChecker $contentBuilderPermissionCheckerService = null;
    private ?ContentBuilderRecordLoader $contentBuilderRecordLoaderService = null;
    private ?QuickModeRendererFactory $quickModeRendererFactoryService = null;
    private ?FormOnloadScriptBuilder $formOnloadScriptBuilderService = null;
    private ?QueryListRowPreparationService $queryListRowPreparationService = null;
    private ?QueryListPageScriptBuilder $queryListPageScriptBuilderService = null;
    private ?CallbackRegistrationService $callbackRegistrationService = null;
    private ?FormPieceExecutionService $formPieceExecutionService = null;
    private ?SubmittedCallbackNameResolver $submittedCallbackNameResolverService = null;

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

    private function classicStaticTextBuilder(): ClassicStaticTextBuilder
    {
        return $this->classicStaticTextBuilderService ??= new ClassicStaticTextBuilder();
    }

    private function classicHiddenInputBuilder(): ClassicHiddenInputBuilder
    {
        return $this->classicHiddenInputBuilderService ??= new ClassicHiddenInputBuilder();
    }

    private function classicTextInputBuilder(): ClassicTextInputBuilder
    {
        return $this->classicTextInputBuilderService ??= new ClassicTextInputBuilder();
    }

    private function classicTextareaBuilder(): ClassicTextareaBuilder
    {
        return $this->classicTextareaBuilderService ??= new ClassicTextareaBuilder();
    }

    private function classicChoiceBuilder(): ClassicChoiceBuilder
    {
        return $this->classicChoiceBuilderService ??= new ClassicChoiceBuilder();
    }

    private function classicSelectBuilder(): ClassicSelectBuilder
    {
        return $this->classicSelectBuilderService ??= new ClassicSelectBuilder();
    }

    private function classicRegularButtonBuilder(): ClassicRegularButtonBuilder
    {
        return $this->classicRegularButtonBuilderService ??= new ClassicRegularButtonBuilder();
    }

    private function classicGraphicButtonBuilder(): ClassicGraphicButtonBuilder
    {
        return $this->classicGraphicButtonBuilderService ??= new ClassicGraphicButtonBuilder();
    }

    private function classicFileUploadBuilder(): ClassicFileUploadBuilder
    {
        return $this->classicFileUploadBuilderService ??= new ClassicFileUploadBuilder();
    }

    private function classicCaptchaBuilder(): ClassicCaptchaBuilder
    {
        return $this->classicCaptchaBuilderService ??= new ClassicCaptchaBuilder();
    }

    private function classicQueryListSettingsBuilder(): ClassicQueryListSettingsBuilder
    {
        return $this->classicQueryListSettingsBuilderService ??= new ClassicQueryListSettingsBuilder();
    }

    private function classicQueryListHeaderBuilder(): ClassicQueryListHeaderBuilder
    {
        return $this->classicQueryListHeaderBuilderService ??= new ClassicQueryListHeaderBuilder();
    }

    private function classicQueryListCellBuilder(): ClassicQueryListCellBuilder
    {
        return $this->classicQueryListCellBuilderService ??= new ClassicQueryListCellBuilder();
    }

    private function classicQueryListRowBuilder(): ClassicQueryListRowBuilder
    {
        return $this->classicQueryListRowBuilderService ??=
            new ClassicQueryListRowBuilder($this->classicQueryListCellBuilder());
    }

    private function classicQueryListFooterBuilder(): ClassicQueryListFooterBuilder
    {
        return $this->classicQueryListFooterBuilderService ??= new ClassicQueryListFooterBuilder();
    }

    private function classicQueryListMarkupBuilder(): ClassicQueryListMarkupBuilder
    {
        return $this->classicQueryListMarkupBuilderService ??= new ClassicQueryListMarkupBuilder();
    }

    private function contentBuilderValueScriptBuilder(): ContentBuilderValueScriptBuilder
    {
        return $this->contentBuilderValueScriptBuilderService ??= new ContentBuilderValueScriptBuilder();
    }

    private function editableRecordHydrationScriptBuilder(): EditableRecordHydrationScriptBuilder
    {
        return $this->editableRecordHydrationScriptBuilderService ??= new EditableRecordHydrationScriptBuilder(
            $this->contentBuilderValueScriptBuilder(),
            fn(string $value): string => (string) InputFilter::getInstance([], [], 1, 1)->clean($value, 'html')
        );
    }

    private function hiddenFormFieldsBuilder(): HiddenFormFieldsBuilder
    {
        return $this->hiddenFormFieldsBuilderService ??= new HiddenFormFieldsBuilder();
    }

    private function legacyScriptTagWrapperBuilder(): LegacyScriptTagWrapperBuilder
    {
        return $this->legacyScriptTagWrapperBuilderService ??= new LegacyScriptTagWrapperBuilder();
    }

    private function contentBuilderReadonlyScriptBuilder(): ContentBuilderReadonlyScriptBuilder
    {
        return $this->contentBuilderReadonlyScriptBuilderService ??= new ContentBuilderReadonlyScriptBuilder();
    }

    private function editableRecordLoader(): EditableRecordLoader
    {
        return $this->editableRecordLoaderService ??= new EditableRecordLoader($this->processor->database);
    }

    private function postRenderScriptBuilder(): PostRenderScriptBuilder
    {
        return $this->postRenderScriptBuilderService ??= new PostRenderScriptBuilder();
    }


    private function contentBuilderTechnicalFieldsBuilder(): ContentBuilderTechnicalFieldsBuilder
    {
        return $this->contentBuilderTechnicalFieldsBuilderService ??= new ContentBuilderTechnicalFieldsBuilder();
    }

    private function buildContentBuilderTechnicalFields(): string
    {
        $input = $this->processor->app->getInput();
        $formId = $input->getInt('cb_form_id', 0);

        return $formId
            ? $this->contentBuilderTechnicalFieldsBuilder()->build(
                '',
                $formId,
                $input->getInt('cb_record_id', 0),
                $input->getBool('cbIsNew', false)
            )
            : '';
    }

    /**
     * @return array<string, int|string>
     */
    private function buildFormContext(bool $includeRunmode): array
    {
        $input = $this->processor->app->getInput();
        $context = [
            'ff_contentid' => $input->getInt('ff_contentid', 0),
            'ff_applic' => $input->getWord('ff_applic', ''),
            'ff_record_id' => $this->processor->record_id,
            'ff_module_id' => $input->getInt('ff_module_id', 0),
        ];

        if ($includeRunmode) {
            $context['ff_runmode'] = $this->processor->runmode;
        }

        return $context;
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

    private function formModeFinalizationBuilder(): FormModeFinalizationBuilder
    {
        return $this->formModeFinalizationBuilderService ??= new FormModeFinalizationBuilder();
    }

    private function quickModeFormTagBuilder(): QuickModeFormTagBuilder
    {
        return $this->quickModeFormTagBuilderService ??= new QuickModeFormTagBuilder();
    }



    private function fileExtensionsCheckBuilder(): FileExtensionsCheckBuilder
    {
        return $this->fileExtensionsCheckBuilderService ??= new FileExtensionsCheckBuilder();
    }

    private function queryListSelectAllScriptBuilder(): QueryListSelectAllScriptBuilder
    {
        return $this->queryListSelectAllScriptBuilderService ??= new QueryListSelectAllScriptBuilder();
    }

    private function queryListNavigationBuilder(): QueryListNavigationBuilder
    {
        return $this->queryListNavigationBuilderService ??= new QueryListNavigationBuilder();
    }

    private function queryListRowsRefreshBuilder(): QueryListRowsRefreshBuilder
    {
        return $this->queryListRowsRefreshBuilderService ??= new QueryListRowsRefreshBuilder();
    }

    private function queryListPaginationTailBuilder(): QueryListPaginationTailBuilder
    {
        return $this->queryListPaginationTailBuilderService ??= new QueryListPaginationTailBuilder();
    }

    private function queryListStateLibraryBuilder(): QueryListStateLibraryBuilder
    {
        return $this->queryListStateLibraryBuilderService ??= new QueryListStateLibraryBuilder();
    }

    private function queryListRowStateBuilder(): QueryListRowStateBuilder
    {
        return $this->queryListRowStateBuilderService ??= new QueryListRowStateBuilder();
    }


    private function paymentProviderDetector(): PaymentProviderDetector
    {
        return $this->paymentProviderDetectorService ??= new PaymentProviderDetector();
    }

    private function captchaSupportBuilder(): CaptchaSupportBuilder
    {
        return $this->captchaSupportBuilderService ??= new CaptchaSupportBuilder();
    }

    private function captchaValidationRowSelector(): CaptchaValidationRowSelector
    {
        return $this->captchaValidationRowSelectorService ??= new CaptchaValidationRowSelector();
    }


    private function captchaLegacyValidationScriptBuilder(): CaptchaLegacyValidationScriptBuilder
    {
        return $this->captchaLegacyValidationScriptBuilderService ??= new CaptchaLegacyValidationScriptBuilder();
    }

    private function captchaReCaptchaValidationScriptBuilder(): CaptchaReCaptchaValidationScriptBuilder
    {
        return $this->captchaReCaptchaValidationScriptBuilderService ??= new CaptchaReCaptchaValidationScriptBuilder();
    }

    private function captchaValidationScriptBuilder(): CaptchaValidationScriptBuilder
    {
        return $this->captchaValidationScriptBuilderService ??= new CaptchaValidationScriptBuilder(
            $this->captchaSupportBuilder(),
            $this->captchaValidationRowSelector(),
            $this->captchaLegacyValidationScriptBuilder(),
            $this->captchaReCaptchaValidationScriptBuilder()
        );
    }

    private function contentBuilderEditableRecordScriptBuilder(): ContentBuilderEditableRecordScriptBuilder
    {
        return $this->contentBuilderEditableRecordScriptBuilderService ??= new ContentBuilderEditableRecordScriptBuilder(
            fn(string $value): string => (string) InputFilter::getInstance([], [], 1, 1)->clean($value, 'html'),
            fn(string $value, int $width, string $break, bool $cut): string =>
                ContentbuilderngHelper::contentbuilderng_wordwrap($value, $width, $break, $cut)
        );
    }

    private function contentBuilderNonEditableFieldsResolver(): ContentBuilderNonEditableFieldsResolver
    {
        return $this->contentBuilderNonEditableFieldsResolverService ??= new ContentBuilderNonEditableFieldsResolver(
            static fn(int $contentBuilderId): array => ListSupportService::createFromRuntimeContext()
                ->getListNonEditableElements($contentBuilderId)
        );
    }

    private function contentBuilderFormAssociationLoader(): ContentBuilderFormAssociationLoader
    {
        return $this->contentBuilderFormAssociationLoaderService ??= new ContentBuilderFormAssociationLoader(
            $this->processor->database
        );
    }

    private function contentBuilderFormDataLoader(): ContentBuilderFormDataLoader
    {
        return $this->contentBuilderFormDataLoaderService ??= new ContentBuilderFormDataLoader(
            $this->processor->database
        );
    }

    private function contentBuilderPermissionChecker(): ContentBuilderPermissionChecker
    {
        return $this->contentBuilderPermissionCheckerService ??= new ContentBuilderPermissionChecker(
            static fn(): ContentBuilderPermissionGateway => new ContentBuilderPermissionServiceAdapter(
                PermissionService::createFromRuntimeContext()
            )
        );
    }

    private function contentBuilderRecordLoader(): ContentBuilderRecordLoader
    {
        return $this->contentBuilderRecordLoaderService ??= new ContentBuilderRecordLoader(
            static function (string $referenceId, int $recordId, bool $publishedOnly, int $ownerId, bool $showAllLanguages): array {
                $form = FormSourceFactory::getForm('com_breezingformsng', $referenceId);

                return (array) $form->getRecord($recordId, $publishedOnly, $ownerId, $showAllLanguages);
            }
        );
    }

    private function quickModeRendererFactory(): QuickModeRendererFactory
    {
        return $this->quickModeRendererFactoryService ??= new QuickModeRendererFactory();
    }

    private function formOnloadScriptBuilder(): FormOnloadScriptBuilder
    {
        return $this->formOnloadScriptBuilderService ??= new FormOnloadScriptBuilder();
    }

    private function queryListRowPreparationService(): QueryListRowPreparationService
    {
        return $this->queryListRowPreparationService ??= new QueryListRowPreparationService(
            $this->processor,
            $this->queryListRowStateBuilder()
        );
    }

    private function queryListPageScriptBuilder(): QueryListPageScriptBuilder
    {
        return $this->queryListPageScriptBuilderService ??= new QueryListPageScriptBuilder(
            $this->queryListRowsRefreshBuilder(),
            $this->queryListNavigationBuilder(),
            $this->queryListPaginationTailBuilder()
        );
    }

    private function callbackRegistrationService(): CallbackRegistrationService
    {
        return $this->callbackRegistrationService ??= new CallbackRegistrationService($this->processor);
    }

    private function formPieceExecutionService(): FormPieceExecutionService
    {
        return $this->formPieceExecutionService ??= new FormPieceExecutionService(
            $this->processor,
            $this->processor->database
        );
    }

    private function submittedCallbackNameResolver(): SubmittedCallbackNameResolver
    {
        return $this->submittedCallbackNameResolverService ??= new SubmittedCallbackNameResolver(
            $this->processor->database
        );
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
            $input = $this->processor->app->getInput();

            if ($this->processor->app->isClient('administrator')) {
                $cbFrontend = false;
            }

            if ($cbFrontend) {
                $this->processor->app->getLanguage()->load('com_contentbuilderng');
            } else {
                $this->processor->app->getLanguage()->load('com_contentbuilderng', JPATH_SITE . '/administrator');
            }

            $db = $this->processor->database;
            $cbForms = $this->contentBuilderFormAssociationLoader()->load((int) $this->processor->form);

            // if no BF form is associated with contentbuilder, we don't need no further checks
            if (!count($cbForms)) {
                return array('form' => $cbForm, 'record' => $cbRecord, 'frontend' => $cbFrontend, 'data' => $cbData, 'full' => $cbFull);
            }

            // test if all published contentbuilder views allow creating new submissions
            if (!$input->getInt('cb_record_id', 0) || !$input->getInt('cb_form_id', 0)) {

                $this->contentBuilderPermissionChecker()->assertCanCreate(
                    $cbForms,
                    $cbFrontend,
                    Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_NEW_NOT_ALLOWED'),
                    Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_NEW_NOT_ALLOWED')
                );
            }

            if ($input->getInt('cb_form_id', 0)) {

                // test the permissions of given record
                $permissionService = $this->contentBuilderPermissionChecker()->assertCanEditOrCreate(
                    $input->getInt('cb_form_id', 0),
                    $input->getInt('cb_record_id', 0),
                    $cbFrontend,
                    Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_EDIT_NOT_ALLOWED'),
                    Text::_('COM_CONTENTBUILDERNG_PERMISSIONS_NEW_NOT_ALLOWED')
                );

                $cbFormId = $input->getInt('cb_form_id', 0);
                $cbData = $this->contentBuilderFormDataLoader()->load($cbFormId);
                if (is_array($cbData)) {
                    $cbFull = $this->contentBuilderPermissionChecker()->canViewFullArticle($permissionService, $cbFrontend);
                    $cbForm = FormSourceFactory::getForm('com_breezingformsng', $cbData['reference_id']);
                    $cbRecord = $this->contentBuilderRecordLoader()->load(
                        $cbData,
                        $input->getInt('cb_record_id', 0),
                        $cbFrontend,
                        (int) $this->processor->app->getIdentity()->get('id', 0),
                        $input->getBool('cbIsNew', false),
                        Text::_('COM_CONTENTBUILDERNG_RECORD_NOT_FOUND')
                    );
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

        require_once JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/functions/helpers.php';

        $rootMdata = $this->loadQuickModeMetadata();

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

        [$fileExtensionsCheck, $cntFiles] = $this->fileExtensionsCheckBuilder()->build(
            $this->processor->rows,
            $this->processor->rowcount,
            json_encode(
                Text::_('COM_BREEZINGFORMSNG_FILE_EXTENSION_NOT_ALLOWED'),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            ),
            trim((string) $this->processor->formrow->template_code) != ''
        );

        $capFunc = $this->captchaValidationScriptBuilder()->build(
            Uri::root(true),
            $this->processor->app->isClient('administrator'),
            (int) $this->processor->form,
            $this->processor->rows,
            $this->processor->rowcount,
            Text::_('COM_BREEZINGFORMSNG_CAPTCHA_MISSING_WRONG')
        );

        echo $this->legacyScriptTagWrapperBuilder()->formValidationOpen(
            $fileExtensionsCheck,
            $capFunc,
            nl()
        );

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
            foreach ($this->queryListStateLibraryBuilder()->build($qcode, nl()) as $entry) {
                $library[] = $entry;
            }

            $library[] = array(
                'ff_selectAllQueryRows',
                $this->queryListSelectAllScriptBuilder()->build(nl())
            );

            $code = $this->queryListPageScriptBuilder()->build(
                [
                    'start' => Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGESTART'),
                    'previous' => Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGEPREV'),
                    'next' => Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGENEXT'),
                    'end' => Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGEEND'),
                ],
                (bool) $qcheckboxes,
                (int) $this->processor->formrow->heightmode,
                (int) $this->processor->formrow->height,
                (bool) $this->processor->inframe,
                nl()
            );
            $this->processor->linkcode('ff_dispQueryPage', $library, $linked, $code);
            if ($this->processor->bury()) {
                $this->abortViewRendering();
                return;
            }
        } // if

        echo $this->legacyScriptTagWrapperBuilder()->formValidationClose(nl());

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
            echo $this->quickModeFormTagBuilder()->build(
                $url,
                (string) $this->processor->form_id,
                $this->processor->formrow->class2 != ''
                    ? $this->processor->getClassName($this->processor->formrow->class2)
                    : '',
                nl()
            );
        } // if

        $js = '';
        $cbJs = '';

        if ($this->processor->editable && $cbRecord === null) {
            $userId = $this->processor->app->getIdentity()->get('id', -1);
            $editableRecord = $this->editableRecordLoader()->load((int) $this->processor->form, (int) $userId);
            if ($editableRecord !== null) {
                $this->processor->record_id = $editableRecord->id;
                $recordEntries = $editableRecord->entries;

                $js = $this->editableRecordHydrationScriptBuilder()->build($recordEntries, (int) $this->processor->form);

                echo $this->legacyScriptTagWrapperBuilder()->editableRecord(
                    (int) $this->processor->form,
                    $js,
                    nl()
                );
            }
        }

        // CONTENTBUILDER BEGIN

        if ($cbRecord !== null) {
            $cbNonEditableFields = $this->contentBuilderNonEditableFieldsResolver()->resolve(
                (int) $cbResult['data']['id']
            );
            $scripts = $this->contentBuilderEditableRecordScriptBuilder()->build(
                $cbRecord,
                $cbNonEditableFields,
                trim((string) $this->processor->formrow->template_code_processed) === 'QuickMode',
                (int) $this->processor->form,
                JPATH_SITE . '/media/breezingforms/signatures/'
            );
            echo $this->legacyScriptTagWrapperBuilder()->contentBuilderEditable(
                (int) $this->processor->form,
                $scripts['contentBuilderScript'],
                $scripts['javascript']
            );
        }

            $cbNonEditableFields = array();
        if ($cbForm !== null) {
            $cbNonEditableFields = $this->contentBuilderNonEditableFieldsResolver()->resolve(
                (int) $cbResult['data']['id']
            );
            if (count($cbNonEditableFields)) {
                $this->processor->app->getDocument()->getWebAssetManager()->addInlineScript('<!--' . nl() . 'var bfDeactivateField = new Array();' . nl() . '//-->');
                echo $this->legacyScriptTagWrapperBuilder()->contentBuilderReadonly(
                    $this->contentBuilderReadonlyScriptBuilder()->build($cbNonEditableFields),
                    nl()
                );
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
                $data1 = '';
                $data2 = '';
                $data3 = '';
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
                        echo $this->classicStaticTextBuilder()->build(
                            (int) $row->id,
                            $attribs,
                            $class1,
                            $data1,
                            indentc(1),
                            nl()
                        );
                        break;
                    case 'Rectangle':
                        echo $this->classicStaticTextBuilder()->buildRectangle(
                            (int) $row->id,
                            $attribs,
                            $class1,
                            $data1,
                            $data2,
                            indentc(1),
                            nl()
                        );
                        break;
                    case 'Image':
                        echo $this->classicStaticTextBuilder()->buildImage(
                            (int) $row->id,
                            $attribs,
                            $class1,
                            $class2,
                            $data1,
                            $data2,
                            (int) $row->width,
                            (int) $row->height,
                            indentc(1),
                            nlc() ?? ''
                        );
                        break;
                    case 'Tooltip':
                        echo $this->classicStaticTextBuilder()->buildTooltip(
                            (int) $row->id,
                            $attribs,
                            $class1,
                            $class2,
                            (string) $row->title,
                            $data2,
                            $data1,
                            (int) $row->flag1,
                            $ff_mossite,
                            indentc(1),
                            nlc() ?? ''
                        );
                        break;
                    case 'Hidden Input':
                        echo $this->classicHiddenInputBuilder()->build(
                            (int) $row->id,
                            (string) $row->name,
                            $data1,
                            indentc(1),
                            nl()
                        );
                        break;
                    case 'Checkbox':
                        echo $this->classicChoiceBuilder()->build(
                            'checkbox',
                            (int) $row->id,
                            (string) $row->name,
                            $data1,
                            $data2,
                            $attribs,
                            $class1,
                            $class2,
                            (bool) $row->flag1,
                            (bool) $row->flag2,
                            $this->processor->script2clause($row),
                            indentc(1),
                            nlc() ?? ''
                        );
                        break;
                    case 'Radio Button':
                        echo $this->classicChoiceBuilder()->build(
                            'radio',
                            (int) $row->id,
                            (string) $row->name,
                            $data1,
                            $data2,
                            $attribs,
                            $class1,
                            $class2,
                            (bool) $row->flag1,
                            (bool) $row->flag2,
                            $this->processor->script2clause($row),
                            indentc(1),
                            nlc() ?? ''
                        );
                        break;
                    case 'Regular Button':
                        echo $this->classicRegularButtonBuilder()->build(
                            (int) $row->id,
                            (string) $row->name,
                            $data2,
                            $attribs,
                            $class1,
                            $class2,
                            (bool) $row->flag2,
                            $this->processor->script2clause($row),
                            indentc(1),
                            nlc() ?? ''
                        );
                        break;
                    case 'Graphic Button':
                        echo $this->classicGraphicButtonBuilder()->build(
                            (int) $row->id,
                            (string) $row->name,
                            $data1,
                            $data2,
                            $attribs,
                            $class1,
                            $class2,
                            (int) $row->width,
                            (int) $row->height,
                            (bool) $row->flag2,
                            $this->processor->script2clause($row),
                            (int) $row->flag1,
                            indentc(1),
                            nlc() ?? ''
                        );
                        break;
                    case 'Icon':
                        echo $this->classicStaticTextBuilder()->buildIcon(
                            (int) $row->id,
                            $attribs,
                            $class1,
                            $class2,
                            $data1,
                            $data2,
                            $data3,
                            $this->processor->script2clause($row),
                            (int) $row->flag1,
                            (bool) $row->flag2,
                            (int) $row->width,
                            (int) $row->height,
                            indentc(1),
                            nlc() ?? ''
                        );
                        break;
                    case 'Select List':
                        echo $this->classicSelectBuilder()->build(
                            (int) $row->id,
                            (string) $row->name,
                            $attribs,
                            $class1,
                            $class2,
                            (string) $data1,
                            $data2,
                            (int) $row->width,
                            (int) $row->height,
                            (bool) $row->flag1,
                            (bool) $row->flag2,
                            $this->processor->script2clause($row),
                            indentc(1),
                            nlc() ?? ''
                        );
                        break;
                    case 'Text':
                        echo $this->classicTextInputBuilder()->build(
                            (int) $row->id,
                            (string) $row->name,
                            $data1,
                            $attribs,
                            $class1,
                            $class2,
                            (int) $row->width,
                            (int) $row->widthmode,
                            (int) $row->height,
                            (bool) $row->flag1,
                            (int) $row->flag2,
                            $this->processor->script2clause($row),
                            indentc(1),
                            nlc() ?? ''
                        );
                        break;
                    case 'Textarea':
                        echo $this->classicTextareaBuilder()->build(
                            (int) $row->id,
                            (string) $row->name,
                            $data1,
                            $attribs,
                            $class1,
                            $class2,
                            (int) $row->width,
                            (int) $row->widthmode,
                            (int) $row->height,
                            (int) $row->heightmode,
                            stristr($this->processor->browser, 'mozilla') !== false,
                            (int) $row->flag2,
                            $this->processor->script2clause($row),
                            indentc(1),
                            nlc() ?? ''
                        );
                        break;
                    case 'File Upload':
                        echo $this->classicFileUploadBuilder()->build(
                            (int) $row->id,
                            (string) $row->name,
                            $attribs,
                            $class1,
                            $class2,
                            (int) $row->width,
                            (int) $row->height,
                            (bool) $row->flag2,
                            $row->data2 !== '' ? $data2 : '',
                            $this->processor->script2clause($row),
                            indentc(1),
                            nlc() ?? ''
                        );
                        break;
                    case 'Captcha':
                        $captcha_url = $this->captchaSupportBuilder()->endpoints(
                            Uri::root(true),
                            $this->processor->app->isClient('administrator'),
                            (int) $this->processor->form
                        )['captcha'];
                        echo $this->classicCaptchaBuilder()->build(
                            (int) $row->id,
                            $attribs,
                            $class1,
                            $captcha_url,
                            Uri::root(),
                            (int) $row->width,
                            (int) $row->height,
                            indentc(1),
                            nlc() ?? '',
                            nl()
                        );
                        break;
                    case 'Query List':
                        $wrapperStyle = $attribs;
                        $queryListSettings = $this->classicQueryListSettingsBuilder()->build(
                            (string) $row->data1,
                            (int) $row->width,
                            fn (string $class): string => $this->processor->getClassName($class)
                        );
                        $trhclass = $queryListSettings['headerClass'];
                        $tr1class = $queryListSettings['oddClass'];
                        $tr2class = $queryListSettings['evenClass'];
                        $trfclass = $queryListSettings['footerClass'];
                        $tdfclass = $queryListSettings['footerCellClass'];
                        $pagenav = $queryListSettings['pageNavigation'];

                        // display 1st page of table
                        echo $this->classicQueryListMarkupBuilder()->open(
                            (int) $row->id,
                            $wrapperStyle,
                            $class1,
                            $queryListSettings['tableAttributes'],
                            $class2,
                            indentc(1),
                            indentc(2),
                            nlc() ?? '',
                            nl()
                        );

                        $cols = &$this->processor->queryCols['ff_' . $row->id];
                        $colcnt = count($cols);

                        // display header
                        if ($row->flag1) {
                            echo $this->classicQueryListHeaderBuilder()->build(
                                $cols,
                                (int) $row->id,
                                (int) $row->flag2,
                                $trhclass,
                                fn (string $class): string => $this->processor->getClassName($class),
                                fn (object $column): string => $this->processor->replaceCode(
                                    $column->title,
                                    Text::_('COM_BREEZINGFORMSNG_PROCESS_QTITLEOF') . " $row->name::$column->name",
                                    'e',
                                    $row->id,
                                    2
                                ),
                                indentc(3),
                                indentc(4),
                                nlc() ?? ''
                            );
                        }
                        // display data rows
                        $qrows = &$this->processor->queryRows['ff_' . $row->id];
                        $qcnt = count($qrows);
                        $k = 1;
                        if ($row->height > 0 && $qcnt > $row->height)
                            $qcnt = $row->height;
                        for ($q = 0; $q < $qcnt; $q++) {
                            $qrow = &$qrows[$q];
                            $cl = $k == 1 ? $tr1class : $tr2class;
                            echo $this->classicQueryListRowBuilder()->build(
                                $cols,
                                $qrow,
                                (int) $row->id,
                                $q,
                                (string) $row->name,
                                $cl,
                                (int) $row->flag2,
                                $k == 1,
                                fn (string $class): string => $this->processor->getClassName($class),
                                fn (): bool => $this->processor->dying,
                                indentc(3),
                                indentc(4),
                                nlc() ?? '',
                                nl()
                            );
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
                            echo $this->classicQueryListFooterBuilder()->build(
                                (int) $row->id,
                                $span,
                                $pages,
                                (int) $pagenav,
                                $trfclass,
                                $tdfclass,
                                Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGESTART'),
                                Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGEPREV'),
                                Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGENEXT'),
                                Text::_('COM_BREEZINGFORMSNG_PROCESS_PAGEEND'),
                                indentc(3),
                                indentc(4),
                                indentc(5),
                                nlc() ?? '',
                                nl()
                            );
                        } // if
                        // table end
                        echo $this->classicQueryListMarkupBuilder()->close(
                            indentc(2),
                            indentc(1),
                            nlc() ?? '',
                            nl()
                        );
                        unset($qrows);
                        unset($cols);
                        break;
                    default:
                        break;
                } // switch
                unset($row);
            } // for
        } else if (trim($this->processor->formrow->template_code_processed) == 'QuickMode') {

            $quickMode = $this->quickModeRendererFactory()->create($this->processor, $rootMdata);
            $this->processor->quickmode = $quickMode;

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
            echo $this->hiddenFormFieldsBuilder()->paymentMethod(indentc(1));
        }

        switch ($this->processor->runmode) {
            case _FF_RUNMODE_FRONTEND:
                $input = $this->processor->app->getInput();
                $context = $this->buildFormContext(false);
                $routing = $this->hiddenFormFieldsBuilder()->routing($input->getString('return', ''), $input->getString('tmpl', ''), nl());
                $technical = $this->buildContentBuilderTechnicalFields();
                echo $this->formModeFinalizationBuilder()->frontend(
                    $this->hiddenFormFieldsBuilder()->context($context, indentc(1)),
                    $this->hiddenFormFieldsBuilder()->submission((int) $this->processor->form, indentc(1), nl()),
                    $this->hiddenFormFieldsBuilder()->token(\Joomla\CMS\HTML\HTMLHelper::_('form.token'), indentc(1), nl()),
                    $this->formOptionalContextFieldsBuilder()->build($this->processor->target, (bool) $this->processor->inframe, (bool) $this->processor->border, $this->processor->page, $this->processor->align, $this->processor->top, indentc(1), true, true, true, true, nl()),
                    $this->hiddenFormFieldsBuilder()->additional($ff_otherparams, indentc(1), nl()),
                    $technical,
                    $routing,
                    nl()
                );
                break;

            case _FF_RUNMODE_BACKEND:
                $input = $this->processor->app->getInput();
                $context = $this->buildFormContext(true);
                $routing = $this->hiddenFormFieldsBuilder()->routing($input->getString('return', ''), $input->getString('tmpl', ''), nl());
                $technical = $this->buildContentBuilderTechnicalFields();
                echo $this->formModeFinalizationBuilder()->backend(
                    $this->hiddenFormFieldsBuilder()->submission((int) $this->processor->form, indentc(1), nl(), true),
                    $this->hiddenFormFieldsBuilder()->token(\Joomla\CMS\HTML\HTMLHelper::_('form.token'), indentc(1), nl()),
                    $this->hiddenFormFieldsBuilder()->context($context, indentc(1)),
                    $this->formOptionalContextFieldsBuilder()->build($this->processor->target, (bool) $this->processor->inframe, (bool) $this->processor->border, $this->processor->page, $this->processor->align, $this->processor->top, indentc(1), true, true, true, true, nl()),
                    $technical,
                    $routing,
                    nl()
                );
                break;

            default:
                if (!$this->processor->inframe) {
                    break;
                }
                $input = $this->processor->app->getInput();
                $context = $this->buildFormContext(true);
                $routing = $this->hiddenFormFieldsBuilder()->routing($input->getString('return', ''), $input->getString('tmpl', ''), nl());
                $technical = $this->buildContentBuilderTechnicalFields();
                echo $this->formModeFinalizationBuilder()->preview(
                    true,
                    $this->hiddenFormFieldsBuilder()->submission((int) $this->processor->form, indentc(1), nl(), false, true),
                    $this->hiddenFormFieldsBuilder()->token(\Joomla\CMS\HTML\HTMLHelper::_('form.token'), indentc(1), nl()),
                    $this->hiddenFormFieldsBuilder()->context($context, indentc(1)),
                    $this->formOptionalContextFieldsBuilder()->build($this->processor->target, (bool) $this->processor->inframe, (bool) $this->processor->border, $this->processor->page, $this->processor->align, $this->processor->top, indentc(1), false, true, false, false, nl()),
                    $technical,
                    $routing,
                    nl()
                );
        }
        if ($this->executeAfterFormPiece()) {
            $this->abortViewRendering();
            return;
        }

        $this->closeFormRendering();
        $this->finishViewRendering();

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
        $code = $this->formOnloadScriptBuilder()->initial(
            $this->processor->formrow->heightmode,
            $this->processor->formrow->height,
            (bool) $this->processor->showgrid,
            nl()
        );
        $this->processor->linkcode('onload', $library, $linked, $code);
    }

    private function initializeFormRendering(): void
    {
        $this->processor->queryCols = [];
        $this->processor->queryRows = [];

        echo $this->formOpeningMarkupBuilder()->build(
            (string) $this->processor->form,
            $this->processor->formrow->class1 != ''
                ? $this->processor->getClassName($this->processor->formrow->class1)
                : ''
        );

        $this->processor->status = $this->processor->app->getInput()->getCmd('ff_status', '');
        $this->processor->message = $this->processor->app->getInput()->getString('ff_message', '');
    }

    private function closeFormRendering(): void
    {
        echo $this->formClosingMarkupBuilder()->build(nl());
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
        return $this->formPieceExecutionService()->executeBefore(
            $this->processor->formrow,
            Text::_('COM_BREEZINGFORMSNG_PROCESS_BFPIECE'),
            Text::_('COM_BREEZINGFORMSNG_PROCESS_BFPIECEC'),
            (int) $this->processor->form
        );
    }

    private function executeAfterFormPiece(): bool
    {
        return $this->formPieceExecutionService()->executeAfter(
            $this->processor->formrow,
            Text::_('COM_BREEZINGFORMSNG_PROCESS_AFPIECE'),
            Text::_('COM_BREEZINGFORMSNG_PROCESS_AFPIECEC'),
            (int) $this->processor->form
        );
    }

    /**
     * Link the onload callback used after a form submission.
     *
     * @param array<int|string, mixed> $library
     * @param array<int|string, mixed> $linked
     */
    private function linkSubmittedOnload(array &$library, array &$linked): void
    {
        $functionName = $this->submittedCallbackNameResolver()->resolve($this->processor->formrow);

        $code = $this->formOnloadScriptBuilder()->submitted(
            (string) $functionName,
            $this->processor->formrow->heightmode,
            $this->processor->formrow->height,
            (bool) $this->processor->showgrid,
            $this->processor->status,
            (string) $this->processor->message,
            nl()
        );
        if ($code === null) {
            return;
        }
        $this->processor->linkcode('onload', $library, $linked, $code);
    }

    /**
     * Prepare the client-side state for one classic Query List element.
     */
    private function prepareQueryListRow(object $row, int &$queryCheckboxCount, string &$queryCode): void
    {
        if ($row->flag2) {
            $queryCheckboxCount++;
        }

        $prepared = $this->queryListRowPreparationService()->prepare($row, nl());
        $this->processor->queryCols[$prepared['key']] = $prepared['columns'];
        $this->processor->queryRows[$prepared['key']] = $prepared['rows'];
        $queryCode .= $prepared['script'];
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
        return $this->callbackRegistrationService()->registerIconBorders($library, $linked, nl());
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
        return $this->callbackRegistrationService()->registerElement($row, $library, $linked);
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
        $this->callbackRegistrationService()->registerStaticTextScan($row, $library, $linked);
    }

    /**
     * Keep the existing characterization seam for the default CAPTCHA data.
     *
     * @return array{0: string, 1: string}
     */
    private function createCaptchaDefaults(): array
    {
        return $this->captchaSupportBuilder()->validationDefaults(
            Text::_('COM_BREEZINGFORMSNG_CAPTCHA_MISSING_WRONG')
        );
    }

    /**
     * Link the form-level init and submission callbacks.
     *
     * @param array<int|string, mixed> $library
     * @param array<int|string, mixed> $linked
     */
    private function addFormScripts(array &$library, array &$linked): bool
    {
        return $this->callbackRegistrationService()->registerForm(
            $this->processor->formrow,
            $library,
            $linked,
            (int) $this->processor->form
        );
    }

    // view

}
