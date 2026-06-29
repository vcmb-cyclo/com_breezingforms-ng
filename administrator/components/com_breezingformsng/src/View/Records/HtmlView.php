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
use Joomla\CMS\Toolbar\Toolbar;
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

    private const ALLOWED_SORTS = [
        'records.id', 'records.submitted', 'forms.title',
        'records.ip', 'records.username',
        'records.viewed', 'records.exported', 'records.archived',
    ];

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $layout = $input->getCmd('layout', 'default');

        HTMLHelper::_('behavior.keepalive');

        if ($layout === 'edit') {
            $this->prepareEditData($input);
            $this->prepareEditToolbar();
        } elseif ($layout === 'csvimport') {
            $this->formSelection = $input->getInt('form_selection', 0);
            $this->prepareImportToolbar();
        } else {
            $this->prepareListData($app, $input);
            $this->prepareListToolbar();
        }

        parent::display($tpl);
    }

    private function prepareListData(\Joomla\CMS\Application\CMSApplication $app, \Joomla\CMS\Input\Input $input): void
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

    private function prepareEditData(\Joomla\CMS\Input\Input $input): void
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
        $exportDropdown = Toolbar::getInstance()
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
        ToolbarHelper::custom('records.viewed', 'eye-open', 'eye-open', Text::_('COM_BREEZINGFORMSNG_TOOLBAR_VIEW'), false);
        ToolbarHelper::custom('records.exported', 'share', 'share', Text::_('COM_BREEZINGFORMSNG_TOOLBAR_EXPORT'), false);
        ToolbarHelper::custom('records.archived', 'archive', 'archive', Text::_('COM_BREEZINGFORMSNG_TOOLBAR_ARCHIVE'), false);
        ToolbarHelper::custom('records.remove', 'delete', 'delete', Text::_('COM_BREEZINGFORMSNG_TOOLBAR_DELETE'), false);
        ToolbarHelper::help(
            'COM_BREEZINGFORMSNG_HELP_RECORDS_TITLE',
            false,
            Uri::base() . 'index.php?option=com_breezingformsng&view=help&section=records&tmpl=component'
        );
    }

    private function prepareEditToolbar(): void
    {
        ToolbarHelper::custom('records.save', 'save', 'save', Text::_('COM_BREEZINGFORMSNG_TOOLBAR_SAVE'), false);
        ToolbarHelper::cancel('records.display', Text::_('COM_BREEZINGFORMSNG_TOOLBAR_CANCEL'));
    }

    private function prepareImportToolbar(): void
    {
        ToolbarHelper::cancel('records.display', Text::_('COM_BREEZINGFORMSNG_TOOLBAR_CANCEL'));
    }
}
