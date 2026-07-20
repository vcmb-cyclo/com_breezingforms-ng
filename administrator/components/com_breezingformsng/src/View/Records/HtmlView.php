<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\View\Records;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Vcmb\Component\BreezingformsNG\Administrator\Model\RecordModel;
use Vcmb\Component\BreezingformsNG\Administrator\Model\RecordsModel;
use Vcmb\Component\BreezingformsNG\Administrator\View\BreezingformsNG\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    // list
    public array $records = [];
    public array $forms = [];
    public int $total = 0;
    public int $formSelection = 0;
    public string $searchTerm = '';
    public int $limitStart = 0;
    public int $limit = 20;
    public string $listOrder = 'records.submitted';
    public string $listDirn = 'desc';

    // edit
    public ?\stdClass $record = null;
    public array $recordRows = [];

    private string $layout = 'default';

    private const ALLOWED_SORTS = [
        'records.id', 'records.submitted', 'forms.title',
        'records.ip', 'records.username',
        'records.viewed', 'records.exported', 'records.archived',
        'records.modified',
    ];

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $layout = $input->getCmd('layout', 'default');
        $this->layout = $layout;

        $app->getDocument()->getWebAssetManager()->useScript('keepalive');

        if ($layout === 'edit') {
            $this->prepareEditData($input);
            $this->prepareEditToolbar();
        } elseif ($layout === 'csvimport') {
            $this->formSelection = $input->getInt('form_selection', 0);
            $this->prepareImportToolbar();
        } else {
            $this->prepareListData($app, $input);
            $this->prepareListToolbar();

            $document = $app->getDocument();
            $document->getWebAssetManager()->registerAndUseScript(
                'com_breezingformsng.admin-sort',
                'media/com_breezingformsng/js/admin/admin-sort.js',
                ['version' => 'auto'],
                ['defer' => true],
                ['core']
            );
            $document->getWebAssetManager()->registerAndUseScript(
                'com_breezingformsng.records-list',
                'media/com_breezingformsng/js/admin/records-list.js',
                ['version' => 'auto'],
                ['defer' => true],
                ['core']
            );
            $document->addScriptOptions(
                'com_breezingformsng.records-list',
                ['csrfToken' => \Joomla\CMS\Session\Session::getFormToken()]
            );
            Text::script('COM_BREEZINGFORMSNG_CONFIRM_DELETE_RECORDS');
            Text::script('COM_BREEZINGFORMSNG_AJAX_STATE_ERROR');
        }

        parent::display($tpl);
    }

    private function prepareListData(\Joomla\CMS\Application\CMSApplication $app, \Joomla\Input\Input $input): void
    {
        $session = $app->getSession();

        $this->formSelection = $input->getInt('form_selection', 0);
        $this->searchTerm = trim((string) $input->getString('searchterm', ''));
        $this->limit = max(1, $input->getInt('limit', 20));
        $this->limitStart = max(0, $input->getInt('limitstart', 0));

        $filterOrder = trim((string) $input->getString('filter_order', ''));
        $filterDir = strtolower(trim((string) $input->getString('filter_order_Dir', '')));

        if (in_array($filterOrder, self::ALLOWED_SORTS, true)) {
            $this->listOrder = $filterOrder;
            $session->set('bf.records_sort', $this->listOrder);
        } else {
            $this->listOrder = (string) $session->get('bf.records_sort', 'records.submitted');
            if (!in_array($this->listOrder, self::ALLOWED_SORTS, true)) {
                $this->listOrder = 'records.submitted';
            }
        }

        if ($filterDir !== '') {
            $this->listDirn = $filterDir === 'asc' ? 'asc' : 'desc';
            $session->set('bf.records_dir', $this->listDirn);
        } else {
            $this->listDirn = (string) $session->get('bf.records_dir', 'desc');
            $this->listDirn = $this->listDirn === 'asc' ? 'asc' : 'desc';
        }

        $factory = $app->bootComponent('com_breezingformsng')->getMVCFactory();
        /** @var RecordsModel $model */
        $model = $factory->createModel('Records', 'Administrator');

        $this->forms = $model->getForms();
        $this->total = $model->getTotal($this->formSelection, $this->searchTerm);
        $this->records = $model->getItems(
            $this->formSelection,
            $this->searchTerm,
            $this->limitStart,
            $this->limit,
            $this->listOrder,
            $this->listDirn
        );

        $app->getDocument()->getWebAssetManager()->useScript('table.columns');
    }

    private function prepareEditData(\Joomla\Input\Input $input): void
    {
        $recordId = $input->getInt('record_id', 0);
        $app = Factory::getApplication();

        if ($recordId < 1) {
            $app->redirect('index.php?option=com_breezingformsng&view=records');
            return;
        }

        $factory = $app->bootComponent('com_breezingformsng')->getMVCFactory();
        /** @var RecordModel $model */
        $model = $factory->createModel('Record', 'Administrator');

        $this->record = $model->getRecord($recordId);
        if (!$this->record) {
            $app->redirect('index.php?option=com_breezingformsng&view=records');
            return;
        }

        $this->recordRows = $model->getEditableRows(
            $recordId,
            (int) $this->record->form,
            (string) $this->record->name
        );
    }

    private function prepareListToolbar(): void
    {
        $toolbar = $this->getDocument()->getToolbar();

        $exportDropdown = $toolbar
            ->dropdownButton('export-options')
            ->text(Text::_('COM_BREEZINGFORMSNG_EXPORT_DOWNLOAD'))
            ->toggleSplit(false)
            ->icon('icon-download')
            ->buttonClass('btn btn-action');
        $child = $exportDropdown->getChildToolbar();
        $child->standardButton('exportPdf')->text(Text::_('COM_BREEZINGFORMSNG_PDF'))->task('records.exportPdf')->icon('icon-download')->listCheck(false);
        $child->standardButton('exportCsv')->text(Text::_('COM_BREEZINGFORMSNG_CSV'))->task('records.exportCsv')->icon('icon-download')->listCheck(false);
        $child->standardButton('exportXml')->text(Text::_('COM_BREEZINGFORMSNG_XML'))->task('records.exportXml')->icon('icon-download')->listCheck(false);

        ToolbarHelper::custom('records.csvImport', 'upload', 'upload', Text::_('COM_BREEZINGFORMSNG_BTN_IMPORT_CSV'), false);

        $markDropdown = $toolbar
            ->dropdownButton('mark-options')
            ->text(Text::_('COM_BREEZINGFORMSNG_TOOLBAR_MARK'))
            ->toggleSplit(false)
            ->icon('icon-check')
            ->buttonClass('btn btn-action');
        $markChild = $markDropdown->getChildToolbar();
        $markChild->standardButton('markViewed')
            ->text(Text::_('COM_BREEZINGFORMSNG_TOOLBAR_MARK_VIEWED'))
            ->task('records.viewed')
            ->icon('icon-eye-open')
            ->attributes(['title' => Text::_('COM_BREEZINGFORMSNG_TOOLBAR_MARK_VIEWED_DESC')])
            ->listCheck(true);
        $markChild->standardButton('unmarkViewed')
            ->text(Text::_('COM_BREEZINGFORMSNG_TOOLBAR_UNMARK_VIEWED'))
            ->task('records.unviewed')
            ->icon('icon-eye-close')
            ->attributes(['title' => Text::_('COM_BREEZINGFORMSNG_TOOLBAR_UNMARK_VIEWED_DESC')])
            ->listCheck(true);
        $markChild->standardButton('markExported')
            ->text(Text::_('COM_BREEZINGFORMSNG_TOOLBAR_MARK_EXPORTED'))
            ->task('records.exported')
            ->icon('icon-share')
            ->attributes(['title' => Text::_('COM_BREEZINGFORMSNG_TOOLBAR_MARK_EXPORTED_DESC')])
            ->listCheck(true);
        $markChild->standardButton('unmarkExported')
            ->text(Text::_('COM_BREEZINGFORMSNG_TOOLBAR_UNMARK_EXPORTED'))
            ->task('records.unexported')
            ->icon('icon-cancel-circle')
            ->attributes(['title' => Text::_('COM_BREEZINGFORMSNG_TOOLBAR_UNMARK_EXPORTED_DESC')])
            ->listCheck(true);
        $markChild->standardButton('markArchived')
            ->text(Text::_('COM_BREEZINGFORMSNG_TOOLBAR_MARK_ARCHIVED'))
            ->task('records.archived')
            ->icon('icon-archive')
            ->attributes(['title' => Text::_('COM_BREEZINGFORMSNG_TOOLBAR_MARK_ARCHIVED_DESC')])
            ->listCheck(true);
        $markChild->standardButton('unmarkArchived')
            ->text(Text::_('COM_BREEZINGFORMSNG_TOOLBAR_UNMARK_ARCHIVED'))
            ->task('records.unarchived')
            ->icon('icon-out-2')
            ->attributes(['title' => Text::_('COM_BREEZINGFORMSNG_TOOLBAR_UNMARK_ARCHIVED_DESC')])
            ->listCheck(true);

        ToolbarHelper::custom('records.remove', 'delete', 'delete', Text::_('COM_BREEZINGFORMSNG_TOOLBAR_DELETE'), false);
        $toolbar
            ->popupButton('help', 'JHELP')
            ->popupType('iframe')
            ->url(Uri::base() . 'index.php?option=com_breezingformsng&view=help&section=records&tmpl=component')
            ->icon('icon-question-sign')
            ->iframeWidth(900)
            ->iframeHeight(700)
            ->attributes(['title' => Text::_('COM_BREEZINGFORMSNG_HELP_RECORDS_TITLE')]);
    }

    private function prepareEditToolbar(): void
    {
        ToolbarHelper::custom('records.save', 'save', 'save', Text::_('COM_BREEZINGFORMSNG_TOOLBAR_SAVE'), false);
        ToolbarHelper::cancel('records.cancel', Text::_('COM_BREEZINGFORMSNG_TOOLBAR_CANCEL'));

        $document = Factory::getApplication()->getDocument();
        $wa       = $document->getWebAssetManager();
        $wa->registerAndUseScript(
            'com_breezingformsng.admin-form',
            'media/com_breezingformsng/js/admin/admin-form.js',
            ['version' => 'auto'],
            ['defer' => true],
            ['core']
        );
        $wa->registerAndUseScript(
            'com_breezingformsng.admin-form-dirty',
            'media/com_breezingformsng/js/admin/admin-form-dirty.js',
            ['version' => 'auto'],
            ['defer' => true],
            ['com_breezingformsng.admin-form']
        );
        $document->addScriptOptions('com_breezingformsng.admin-form', [
            'cancelTask' => 'records.cancel',
            'saveTask'   => 'records.save',
        ]);
        Text::script('COM_BREEZINGFORMSNG_TEST_NO_CHANGES');
        Text::script('COM_BREEZINGFORMSNG_CONFIRM_DISCARD_CHANGES');
    }

    private function prepareImportToolbar(): void
    {
        ToolbarHelper::cancel('records.display', Text::_('COM_BREEZINGFORMSNG_TOOLBAR_CANCEL'));
    }

    protected function getDetailLabel(): ?string
    {
        if ($this->layout === 'edit') {
            $recordId = Factory::getApplication()->getInput()->getInt('record_id', 0);

            return $recordId > 0 ? (string) $recordId : null;
        }

        if ($this->layout === 'csvimport') {
            return Text::_('COM_BREEZINGFORMSNG_BTN_IMPORT_CSV');
        }

        return null;
    }
}
