<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use Vcmb\Component\BreezingformsNG\Administrator\Model\RecordModel;
use Vcmb\Component\BreezingformsNG\Administrator\Service\AjaxStateService;
use Vcmb\Component\BreezingformsNG\Administrator\Service\PdfDocument;
use Vcmb\Component\BreezingformsNG\Administrator\Service\PdfFontDirectoryScanner;

/** @property CMSApplication $app */
class RecordsController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        $this->app->getInput()->set('view', 'records');
        return parent::display($cachable, $urlparams);
    }

    public function edit(): void
    {
        $input = $this->app->getInput();
        $this->app->redirect(
            'index.php?option=com_breezingformsng&view=records&layout=edit'
            . '&record_id=' . $input->getInt('record_id', 0)
            . $this->listStateQuery($input)
        );
    }

    public function cancel(): void
    {
        $this->checkToken();
        $this->app->redirect($this->listUrl($this->app->getInput()));
    }

    public function save(): void
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $recordId = $input->getInt('record_id', 0);

        if ($recordId > 0) {
            $values = $input->post->get('element', [], 'array');
            $this->getRecordModel()->saveRecord(
                $recordId,
                is_array($values) ? $values : [],
                $this->getTimezone()
            );
        }

        $app->redirect(
            'index.php?option=com_breezingformsng&view=records&layout=edit'
            . '&record_id=' . $recordId
            . $this->listStateQuery($input)
        );
    }

    public function remove(): void
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $ids = $input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($ids);
        $contentComponent = $app->bootComponent('com_content');

        if (!$contentComponent instanceof MVCFactoryServiceInterface) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }

        $contentFactory = $contentComponent->getMVCFactory();
        $this->getRecordModel()->deleteRecords($ids, $contentFactory);
        $app->redirect($this->listUrl($input));
    }

    public function viewed(): void     { $this->batchFlag('viewed', 1); }
    public function unviewed(): void   { $this->batchFlag('viewed', 0); }
    public function exported(): void   { $this->batchFlag('exported', 1); }
    public function unexported(): void { $this->batchFlag('exported', 0); }
    public function archived(): void   { $this->batchFlag('archived', 1); }
    public function unarchived(): void { $this->batchFlag('archived', 0); }

    public function setFlag(): void
    {
        $this->checkToken();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $input = $this->app->getInput();
        $recordId = $input->getInt('record_id', 0);
        $column = AjaxStateService::normalizeRecordColumn($input->getString('column', ''));
        $flag = AjaxStateService::normalizeState($input->getInt('flag', 0));
        if ($recordId > 0 && $column !== null) {
            $this->getRecordModel()->setFlagSingle($recordId, $column, $flag);
        }
        $payload = $recordId > 0 && $column !== null
            ? AjaxStateService::success($flag)
            : AjaxStateService::error(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        $this->app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        echo new JsonResponse($payload);
        $this->app->close();
    }

    public function csvImport(): void
    {
        $input = $this->app->getInput();
        $input->set('view', 'records');
        $input->set('layout', 'csvimport');
        parent::display();
    }

    public function setCsvImport(): void
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $formId = $input->getInt('form_id', 0);
        $formSelection = $input->getInt('form_selection', $formId);

        if ($formId < 1) {
            $app->redirect($this->listUrl($input));
            return;
        }

        $encoding = $input->getString('encoding', '0');
        $upload = $input->files->get('csv_file', [], 'array');
        $tmpFile = is_array($upload) ? (string) ($upload['tmp_name'] ?? '') : '';

        if ($tmpFile === '' || !is_uploaded_file($tmpFile)) {
            $app->redirect($this->listUrl($input));
            return;
        }

        $this->getRecordModel()->importCsv($formId, $tmpFile, $encoding);

        $app->redirect('index.php?option=com_breezingformsng&view=records&form_selection=' . $formSelection);
    }

    public function exportPdf(): void
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $ids = $input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($ids);
        $formSelection = $input->getInt('form_selection', 0);

        $model = $this->getRecordModel();
        $tz = $this->getTimezone();
        $db = $model->getDatabaseConnection();

        $recs = $this->fetchRecords($db, $ids, $formSelection);

        $formName = ($formSelection && $recs) ? ($recs[0]->name ?? '') : '';

        $file = JPATH_SITE . '/media/breezingforms/pdftpl/export_custom_pdf.php';
        if (!file_exists($file)) {
            $file = JPATH_ADMINISTRATOR . '/components/com_breezingformsng/pdftpl/export_pdf.php';
        }
        if ($formName !== '') {
            $custom = JPATH_SITE . '/media/breezingforms/pdftpl/' . $formName . '_export_pdf.php';
            if (file_exists($custom)) {
                $file = $custom;
            }
        }

        $updIds = [];
        foreach ($recs as $i => $rec) {
            $updIds[] = (int) $rec->id;
            $date = new \Joomla\CMS\Date\Date($rec->submitted, $tz);
            $offset = $date->getOffsetFromGMT();
            if ($offset > 0) {
                $date->add(new \DateInterval('PT' . $offset . 'S'));
            } elseif ($offset < 0) {
                $date->sub(new \DateInterval('PT' . abs($offset) . 'S'));
            }
            $recs[$i]->submitted = $date->format('Y-m-d H:i:s', true);
        }

        if ($updIds) {
            $model->markExported($updIds);
        }

        $datestamp = new \Joomla\CMS\Date\Date('now', $tz);
        $dsOffset = $datestamp->getOffsetFromGMT();
        if ($dsOffset > 0) {
            $datestamp->add(new \DateInterval('PT' . $dsOffset . 'S'));
        } elseif ($dsOffset < 0) {
            $datestamp->sub(new \DateInterval('PT' . abs($dsOffset) . 'S'));
        }

        $pdf = new PdfDocument();
        $pdf->setFormName($formName);
        $pdf->setWhich('export');

        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        require_once $file;
        $content = ob_get_clean();

        $activeFound = false;
        $ttfName = '';
        $fontDir = JPATH_SITE . '/media/breezingforms/pdftpl/fonts/';
        foreach ((new PdfFontDirectoryScanner())->scan($fontDir) as $f) {
                $lower = strtolower($f);
                if (str_ends_with($lower, '.php')) {
                    $parts = explode('.', $f);
                    array_pop($parts);
                    $pdf->AddFont(implode('_', $parts), '', $fontDir . $f);
                }
                if (str_ends_with($lower, '.ttf')) {
                    $ttfName = PdfDocument::importTtfFont($fontDir . $f);
                }
                if (str_ends_with($lower, '_active')) {
                    $parts = explode('_', $f);
                    array_pop($parts);
                    $pdf->SetFont($ttfName ?: implode('_', $parts));
                    $activeFound = true;
                }
        }

        if (!$activeFound) {
            PdfDocument::importTtfFont(JPATH_SITE . '/media/com_breezingformsng/fonts/verdana.ttf');
            $pdf->SetFont('verdana');
        }

        $pdf->setPrintFooter($pdf->getFooterTemplate() !== '');
        $pdf->setPrintHeader($pdf->getHeaderTemplate() !== '');
        $pdf->AddPage();
        $pdf->writeHTML($content);
        $pdfName = ($formName ? $formName . '-' : '') . 'ffexport-pdf-' . $datestamp->format('YmdHis', true) . '.pdf';
        $pdf->lastPage();
        $pdf->Output($pdfName, 'D');
        $app->close();
    }

    /**
     * Exposed for the PDF export templates, which call $this->getSubrecords().
     */
    public function getSubrecords(int $recordId): array
    {
        return $this->getRecordModel()->getSubrecords($recordId);
    }

    public function exportCsv(): void
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $ids = $input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($ids);
        $formSelection = $input->getInt('form_selection', 0);

        $model = $this->getRecordModel();
        $config = $model->getExportConfig();
        $tz = $this->getTimezone();
        $db = $model->getDatabaseConnection();

        $delimiter = stripslashes((string) $config->csvdelimiter);
        $quote = stripslashes((string) $config->csvquote);
        $cellNewline = ((int) $config->cellnewline === 0) ? "\n" : "\\n";

        $recs = $this->fetchRecords($db, $ids, $formSelection);

        if ($ids) {
            $formIds = array_unique(array_map(fn($r) => (int) $r->form, $recs));
        } elseif ($formSelection) {
            $formIds = [$formSelection];
        } else {
            $formIds = [];
        }

        $elementFields = $this->fetchElementFields($db, $formIds);
        $formName = ($formSelection && $recs) ? ($recs[0]->name ?? '') : '';

        $headKeys = [];
        $seenKeys = [];
        foreach ($elementFields as $ef) {
            $key = md5(strip_tags((string) $ef->name));
            if (!isset($seenKeys[$key])) {
                $seenKeys[$key] = true;
                $headKeys[$key] = strip_tags((string) $ef->name);
            }
        }

        $q = fn($val) => $quote
            . str_replace($quote, $quote . $quote, str_replace("\n", $cellNewline, str_replace("\r", '', (string) $val)))
            . $quote;

        $fixedLabels = ['id', 'submitted', 'user_id', 'username', 'user_full_name', 'bf_form_title', 'ip', 'browser', 'opsys', 'paypal_tx_id', 'paypal_payment_date', 'paypal_testaccount', 'paypal_download_tries', 'double_opt_in'];
        $header = implode($delimiter, array_map($q, $fixedLabels));
        foreach ($headKeys as $name) {
            $header .= $delimiter . $q($name);
        }
        $header .= "\n";

        $updIds = [];
        $body = '';
        foreach ($recs as $rec) {
            $updIds[] = (int) $rec->id;
            $date = new \Joomla\CMS\Date\Date($rec->submitted, $tz);
            $offset = $date->getOffsetFromGMT();
            if ($offset > 0) {
                $date->add(new \DateInterval('PT' . $offset . 'S'));
            } elseif ($offset < 0) {
                $date->sub(new \DateInterval('PT' . abs($offset) . 'S'));
            }

            $subs = $model->getSubrecords((int) $rec->id);
            $subValues = [];
            foreach ($subs as $sub) {
                $k = md5(strip_tags((string) $sub->name));
                $subValues[$k][] = (string) $sub->value;
            }

            $cells = [
                $rec->id,
                $date->format('Y-m-d H:i:s', true),
                $rec->user_id,
                $rec->username,
                $rec->user_full_name,
                $rec->title,
                $rec->ip,
                $rec->browser,
                $rec->opsys,
                $rec->paypal_tx_id,
                $rec->paypal_payment_date,
                $rec->paypal_testaccount,
                $rec->paypal_download_tries,
                $rec->opted ?? '',
            ];
            $row = implode($delimiter, array_map($q, $cells));
            foreach ($headKeys as $key => $name) {
                $val = isset($subValues[$key]) ? implode('|', $subValues[$key]) : '';
                $row .= $delimiter . $q($val);
            }
            $body .= $row . "\n";
        }

        if ($updIds) {
            $model->markExported($updIds);
        }

        $fileName = ($formName ? $formName . '-' : '') . 'ffexport-' . date('YmdHis') . '.csv';

        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        $app->setHeader('Pragma', 'public', true);
        $app->setHeader('Expires', '0', true);
        $app->setHeader('Cache-Control', 'private', true);
        $app->setHeader('Content-Type', 'text/csv; charset=UTF-8', true);
        $app->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true);
        $app->sendHeaders();
        echo "\xEF\xBB\xBF";
        echo $header . $body;
        $app->close();
    }

    public function exportXml(): void
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $ids = $input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($ids);
        $formSelection = $input->getInt('form_selection', 0);

        $model = $this->getRecordModel();
        $tz = $this->getTimezone();
        $db = $model->getDatabaseConnection();

        $recs = $this->fetchRecords($db, $ids, $formSelection);
        $formName = ($formSelection && $recs) ? ($recs[0]->name ?? '') : '';

        $datestamp = new \Joomla\CMS\Date\Date('now', $tz);
        $dsOffset = $datestamp->getOffsetFromGMT();
        if ($dsOffset > 0) {
            $datestamp->add(new \DateInterval('PT' . $dsOffset . 'S'));
        } elseif ($dsOffset < 0) {
            $datestamp->sub(new \DateInterval('PT' . abs($dsOffset) . 'S'));
        }

        $ind = fn(int $n) => str_repeat('  ', $n);

        $xml = '<?xml version="1.0" encoding="utf-8" ?>' . "\n"
            . '<FacileFormsExport type="records">' . "\n"
            . $ind(1) . '<exportdate>' . $datestamp->format('Y-m-d H:i:s', true) . '</exportdate>' . "\n";

        $updIds = [];
        foreach ($recs as $rec) {
            $updIds[] = (int) $rec->id;
            $date = new \Joomla\CMS\Date\Date($rec->submitted, $tz);
            $offset = $date->getOffsetFromGMT();
            if ($offset > 0) {
                $date->add(new \DateInterval('PT' . $offset . 'S'));
            } elseif ($offset < 0) {
                $date->sub(new \DateInterval('PT' . abs($offset) . 'S'));
            }

            $xml .= $ind(1) . '<record id="' . (int) $rec->id . '">' . "\n"
                . $ind(2) . '<submitted>' . $date->format('Y-m-d H:i:s', true) . '</submitted>' . "\n"
                . $ind(2) . '<user_id>' . (int) $rec->user_id . '</user_id>' . "\n"
                . $ind(2) . '<username>' . htmlspecialchars((string) $rec->username) . '</username>' . "\n"
                . $ind(2) . '<user_full_name>' . htmlspecialchars((string) $rec->user_full_name) . '</user_full_name>' . "\n"
                . $ind(2) . '<form>' . (int) $rec->form . '</form>' . "\n"
                . $ind(2) . '<title>' . htmlspecialchars((string) $rec->title) . '</title>' . "\n"
                . $ind(2) . '<name>' . htmlspecialchars((string) $rec->name) . '</name>' . "\n"
                . $ind(2) . '<ip>' . htmlspecialchars((string) $rec->ip) . '</ip>' . "\n"
                . $ind(2) . '<browser>' . htmlspecialchars((string) $rec->browser) . '</browser>' . "\n"
                . $ind(2) . '<opsys>' . htmlspecialchars((string) $rec->opsys) . '</opsys>' . "\n"
                . $ind(2) . '<provider>' . htmlspecialchars((string) ($rec->provider ?? '')) . '</provider>' . "\n"
                . $ind(2) . '<viewed>' . (int) $rec->viewed . '</viewed>' . "\n"
                . $ind(2) . '<exported>' . (int) $rec->exported . '</exported>' . "\n"
                . $ind(2) . '<archived>' . (int) $rec->archived . '</archived>' . "\n"
                . $ind(2) . '<pptxid>' . htmlspecialchars((string) $rec->paypal_tx_id) . '</pptxid>' . "\n"
                . $ind(2) . '<pppdate>' . htmlspecialchars((string) $rec->paypal_payment_date) . '</pppdate>' . "\n"
                . $ind(2) . '<pptestacc>' . (int) $rec->paypal_testaccount . '</pptestacc>' . "\n"
                . $ind(2) . '<ppdltries>' . (int) $rec->paypal_download_tries . '</ppdltries>' . "\n"
                . $ind(2) . '<opted>' . (int) ($rec->opted ?? 0) . '</opted>' . "\n";

            foreach ($model->getSubrecords((int) $rec->id) as $sub) {
                $value = (string) $sub->value;
                if ($sub->type === 'File Upload' && stripos($value, '{cbsite}') !== false) {
                    $value = str_ireplace('{cbsite}', JPATH_SITE, $value);
                }
                $xml .= $ind(2) . '<subrecord id="' . (int) $sub->id . '">' . "\n"
                    . $ind(3) . '<element>' . (int) $sub->element . '</element>' . "\n"
                    . $ind(3) . '<name>' . htmlspecialchars((string) $sub->name) . '</name>' . "\n"
                    . $ind(3) . '<title>' . htmlspecialchars((string) $sub->title) . '</title>' . "\n"
                    . $ind(3) . '<type>' . htmlspecialchars((string) $sub->type) . '</type>' . "\n"
                    . $ind(3) . '<value>' . htmlspecialchars($value) . '</value>' . "\n"
                    . $ind(2) . '</subrecord>' . "\n";
            }
            $xml .= $ind(1) . '</record>' . "\n";
        }
        $xml .= '</FacileFormsExport>' . "\n";

        if ($updIds) {
            $model->markExported($updIds);
        }

        $fileName = ($formName ? $formName . '-' : '') . 'ffexport-' . $datestamp->format('YmdHis', true) . '.xml';

        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        $app->setHeader('Pragma', 'public', true);
        $app->setHeader('Expires', '0', true);
        $app->setHeader('Cache-Control', 'private', true);
        $app->setHeader('Content-Type', 'application/octet-stream', true);
        $app->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true);
        $app->sendHeaders();
        echo $xml;
        $app->close();
    }

    private function batchFlag(string $column, int $value = 1): void
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $ids = $input->post->get('cid', [], 'array');
        ArrayHelper::toInteger($ids);
        $this->getRecordModel()->setFlagsBatch($ids, $column, $value);

        $keyBases = [
            'viewed'   => $value ? 'COM_BREEZINGFORMSNG_RECORDS_N_MARKED_VIEWED' : 'COM_BREEZINGFORMSNG_RECORDS_N_UNMARKED_VIEWED',
            'exported' => $value ? 'COM_BREEZINGFORMSNG_RECORDS_N_MARKED_EXPORTED' : 'COM_BREEZINGFORMSNG_RECORDS_N_UNMARKED_EXPORTED',
            'archived' => $value ? 'COM_BREEZINGFORMSNG_RECORDS_N_MARKED_ARCHIVED' : 'COM_BREEZINGFORMSNG_RECORDS_N_UNMARKED_ARCHIVED',
        ];
        if (isset($keyBases[$column])) {
            $app->enqueueMessage(Text::plural($keyBases[$column], count($ids)), 'message');
        }

        $app->redirect($this->listUrl($input));
    }

    private function getRecordModel(): RecordModel
    {
        $component = $this->app->bootComponent('com_breezingformsng');

        if (!$component instanceof MVCFactoryServiceInterface) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }

        $model = $component->getMVCFactory()->createModel('Record', 'Administrator');

        if (!$model instanceof RecordModel) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }

        return $model;
    }

    private function getTimezone(): \DateTimeZone
    {
        return new \DateTimeZone((string) $this->app->get('offset', 'UTC'));
    }

    /**
     * Shared by exportPdf()/exportCsv()/exportXml(): fetch the records
     * matching either an explicit id selection, a form filter, or all
     * records, always ordered by submission date descending.
     */
    private function fetchRecords(DatabaseInterface $db, array $ids, int $formSelection): array
    {
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__facileforms_records'))
            ->order($db->quoteName('submitted') . ' DESC');

        if ($ids) {
            $query->whereIn($db->quoteName('id'), $ids, ParameterType::INTEGER);
        } elseif ($formSelection) {
            $query->where($db->quoteName('form') . ' = :formSelection')
                ->bind(':formSelection', $formSelection, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Shared by exportCsv(): the distinct published element fields for the
     * form(s) covered by the current export selection.
     */
    private function fetchElementFields(DatabaseInterface $db, array $formIds): array
    {
        $query = $db->getQuery(true)
            ->select('DISTINCT *')
            ->from($db->quoteName('#__facileforms_elements'))
            ->where($db->quoteName('published') . ' = 1')
            ->whereNotIn($db->quoteName('name'), ['bfFakeName', 'bfFakeName2', 'bfFakeName3', 'bfFakeName4', 'bfFakeName5'], ParameterType::STRING)
            ->order($db->quoteName('ordering'));

        if ($formIds) {
            $query->whereIn($db->quoteName('form'), $formIds, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    private function listUrl(\Joomla\Input\Input $input): string
    {
        return 'index.php?option=com_breezingformsng&view=records' . $this->listStateQuery($input);
    }

    private function listStateQuery(\Joomla\Input\Input $input): string
    {
        $query = '&form_selection=' . $input->getInt('form_selection', 0);
        $searchTerm = trim((string) $input->getString('searchterm', ''));
        $filterOrder = trim((string) $input->getString('filter_order', ''));
        $filterDir = strtolower(trim((string) $input->getString('filter_order_Dir', '')));

        if ($searchTerm !== '') {
            $query .= '&searchterm=' . rawurlencode($searchTerm);
        }
        if ($filterOrder !== '') {
            $query .= '&filter_order=' . rawurlencode($filterOrder);
        }
        if ($filterDir !== '') {
            $query .= '&filter_order_Dir=' . rawurlencode($filterDir);
        }

        $query .= '&limit=' . max(1, $input->getInt('limit', 20));
        $query .= '&limitstart=' . max(0, $input->getInt('limitstart', 0));

        return $query;
    }
}
