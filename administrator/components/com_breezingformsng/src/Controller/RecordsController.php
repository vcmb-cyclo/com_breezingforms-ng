<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Utilities\ArrayHelper;
use Vcmb\Component\BreezingformsNG\Administrator\Model\RecordModel;

class RecordsController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        Factory::getApplication()->getInput()->set('view', 'records');
        return parent::display($cachable, $urlparams);
    }

    public function edit(): void
    {
        $input = Factory::getApplication()->getInput();
        Factory::getApplication()->redirect(
            'index.php?option=com_breezingformsng&view=records&layout=edit'
            . '&record_id=' . $input->getInt('record_id', 0)
            . '&form_selection=' . $input->getInt('form_selection', 0)
        );
    }

    public function save(): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $recordId = $input->getInt('record_id', 0);
        $formSelection = $input->getInt('form_selection', 0);

        if ($recordId > 0) {
            $values = $input->get('element', [], 'post', 'array');
            $this->getRecordModel()->saveRecord($recordId, is_array($values) ? $values : []);
        }

        $app->redirect(
            'index.php?option=com_breezingformsng&view=records&layout=edit'
            . '&record_id=' . $recordId
            . '&form_selection=' . $formSelection
        );
    }

    public function remove(): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $ids = $input->get('cid', [], 'post', 'array');
        ArrayHelper::toInteger($ids);
        $this->getRecordModel()->deleteRecords($ids);
        $app->redirect($this->listUrl($input));
    }

    public function viewed(): void   { $this->batchFlag('viewed'); }
    public function exported(): void { $this->batchFlag('exported'); }
    public function archived(): void { $this->batchFlag('archived'); }

    public function setFlag(): void
    {
        @ob_end_clean();
        $input = Factory::getApplication()->getInput();
        $recordId = $input->getInt('record_id', 0);
        $column = $input->getString('column', '');
        $flag = $input->getInt('flag', 0);
        if ($recordId > 0) {
            $this->getRecordModel()->setFlagSingle($recordId, $column, $flag);
        }
        echo json_encode(['Result' => 'OK']);
        Factory::getApplication()->close();
    }

    public function csvImport(): void
    {
        $input = Factory::getApplication()->getInput();
        $input->set('view', 'records');
        $input->set('layout', 'csvimport');
        parent::display();
    }

    public function setCsvImport(): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $formId = $input->getInt('form_id', 0);
        $formSelection = $input->getInt('form_selection', $formId);

        if ($formId < 1) {
            $app->redirect($this->listUrl($input));
            return;
        }

        $encoding = $_POST['encoding'] ?? '0';
        $tmpFile = $_FILES['csv_file']['tmp_name'] ?? '';

        if (!$tmpFile || !@fopen($tmpFile, 'r')) {
            $app->redirect($this->listUrl($input));
            return;
        }

        if ($encoding !== '0' && function_exists('iconv')) {
            $content = iconv($encoding, 'UTF-8//TRANSLIT', file_get_contents($tmpFile));
            $handle = fopen('php://memory', 'rw');
            fwrite($handle, $content);
            fseek($handle, 0);
        } else {
            $handle = fopen($tmpFile, 'rb');
        }

        $lines = [];
        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line !== false) {
                $lines[] = $line;
            }
        }
        fclose($handle);

        if (empty($lines[0]) || trim($lines[0]) === '') {
            $app->redirect($this->listUrl($input));
            return;
        }

        $firstLine = strtolower(str_replace('"', '', $lines[0]));
        $title = explode(';', $firstLine);
        if (count($title) <= 1) {
            $app->redirect($this->listUrl($input));
            return;
        }

        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $db->setQuery("Select `title`, `name` From #__facileforms_forms Where id = " . $formId);
        $theForm = $db->loadObject();

        $fixedColumns = 'id, submitted, form, title, name, ip, browser, opsys, provider, viewed, exported, archived, user_id, username, user_full_name, paypal_tx_id, paypal_payment_date, paypal_testaccount, paypal_download_tries';
        $fixedKeys = explode(', ', strtolower($fixedColumns));
        $fixedKeys[3] = 'bf_form_title';
        $fixedKeys[4] = 'bf_form_name';
        $identity = $app->getIdentity();

        foreach (array_slice($lines, 1) as $rawLine) {
            $record = str_replace('"', '', explode('";"', $rawLine));
            if (count($record) <= 1) {
                continue;
            }

            $values = [];
            foreach ($fixedKeys as $ci => $col) {
                $values[$col] = match ($col) {
                    'id'                    => null,
                    'form'                  => $formId,
                    'bf_form_title'         => in_array($col, $title) ? ($record[array_search($col, $title)] ?? '') : ($theForm->title ?? ''),
                    'bf_form_name'          => in_array($col, $title) ? ($record[array_search($col, $title)] ?? '') : ($theForm->name ?? ''),
                    'submitted'             => in_array($col, $title) ? ($record[array_search($col, $title)] ?? date('Y-m-d H:i:s')) : date('Y-m-d H:i:s'),
                    'ip'                    => in_array($col, $title) ? ($record[array_search($col, $title)] ?? '') : ($_SERVER['REMOTE_ADDR'] ?? ''),
                    'user_id'               => in_array($col, $title) ? (int) ($record[array_search($col, $title)] ?? 0) : (int) $identity->id,
                    'username'              => in_array($col, $title) ? ($record[array_search($col, $title)] ?? '') : (string) $identity->username,
                    'viewed', 'exported', 'archived', 'paypal_testaccount', 'paypal_download_tries'
                                            => (int) (in_array($col, $title) && !empty($record[array_search($col, $title)])),
                    'paypal_payment_date'   => (function () use ($col, $title, $record) {
                        $ji = array_search($col, $title);
                        $val = ($ji !== false) ? trim($record[$ji] ?? '') : '';
                        return ($val && $val !== '-') ? $val : '1970-01-01 00:00:00';
                    })(),
                    default                 => in_array($col, $title) ? ($record[array_search($col, $title)] ?? '') : '',
                };
            }

            $cols = array_keys(array_filter($values, fn($v) => $v !== null));
            $real_cols = array_map(fn($c) => match ($c) { 'bf_form_title' => 'title', 'bf_form_name' => 'name', default => $c }, $cols);

            $query = 'Insert Into #__facileforms_records (' . implode(', ', $real_cols) . ') Values ('
                . implode(', ', array_map(fn($c) => $db->quote($values[$c]), $cols))
                . ')';
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (\RuntimeException) {
                continue;
            }

            $db->setQuery('Select MAX(id) From #__facileforms_records');
            $lastId = (int) $db->loadResult();

            $dlIndex = array_search('download_tries', $title);
            $startIndex = $dlIndex !== false ? $dlIndex + 1 : count($fixedKeys);

            for ($si = $startIndex; $si < count($record); $si++) {
                $fieldName = trim($title[$si] ?? '');
                if ($fieldName === '') {
                    continue;
                }
                $db->setQuery("Select id, title, type From #__facileforms_elements Where form = " . $formId . " And `name` = " . $db->quote($fieldName));
                $element = $db->loadAssoc();
                $db->setQuery(
                    'Insert Into #__facileforms_subrecords (record, element, title, name, type, value) Values ('
                    . $db->quote($lastId) . ', '
                    . $db->quote($element['id'] ?? 0) . ', '
                    . $db->quote($element['title'] ?? '') . ', '
                    . $db->quote($fieldName) . ', '
                    . $db->quote($element['type'] ?? '') . ', '
                    . $db->quote($record[$si] ?? '') . ')'
                );
                try {
                    $db->execute();
                } catch (\RuntimeException) {
                    // continue
                }
            }
        }

        $app->redirect('index.php?option=com_breezingformsng&view=records&form_selection=' . $formSelection);
    }

    public function exportPdf(): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $ids = $input->get('cid', [], 'post', 'array');
        ArrayHelper::toInteger($ids);
        $formSelection = $input->getInt('form_selection', 0);

        $model = $this->getRecordModel();
        $tz = $model->getTimezone();
        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);

        if ($ids) {
            $db->setQuery("Select * From #__facileforms_records Where id In (" . implode(',', $ids) . ") Order By submitted Desc");
        } elseif ($formSelection) {
            $db->setQuery("Select * From #__facileforms_records Where form = " . $formSelection . " Order By submitted Desc");
        } else {
            $db->setQuery("Select * From #__facileforms_records Order By submitted Desc");
        }
        $recs = $db->loadObjectList();

        $formName = ($formSelection && $recs) ? ($recs[0]->name ?? '') : '';

        $file = JPATH_SITE . '/media/breezingforms/pdftpl/export_custom_pdf.php';
        if (!file_exists($file)) {
            $file = JPATH_SITE . '/media/breezingforms/pdftpl/export_pdf.php';
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
            $date = Factory::getDate($rec->submitted, $tz);
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

        if (!class_exists('BFPDF')) {
            require_once JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/classes/BFPDF.php';
        }

        $datestamp = Factory::getDate('now', $tz);
        $dsOffset = $datestamp->getOffsetFromGMT();
        if ($dsOffset > 0) {
            $datestamp->add(new \DateInterval('PT' . $dsOffset . 'S'));
        } elseif ($dsOffset < 0) {
            $datestamp->sub(new \DateInterval('PT' . abs($dsOffset) . 'S'));
        }

        $pdf = new \BFPDF();
        $pdf->setFormName($formName);
        $pdf->setWhich('export');

        @ob_end_clean();
        ob_start();
        require_once $file;
        $content = ob_get_clean();

        $activeFound = false;
        $ttfName = '';
        $fontDir = JPATH_SITE . '/media/breezingforms/pdftpl/fonts/';
        if (is_dir($fontDir) && ($dh = @opendir($fontDir))) {
            while (false !== ($f = @readdir($dh))) {
                if ($f === '.' || $f === '..') {
                    continue;
                }
                $lower = strtolower($f);
                if (str_ends_with($lower, '.php')) {
                    $parts = explode('.', $f);
                    array_pop($parts);
                    $pdf->AddFont(implode('_', $parts), '', $fontDir . $f);
                }
                if (str_ends_with($lower, '.ttf')) {
                    $ttfName = \TCPDF_FONTS::addTTFfont($fontDir . $f, 'TrueTypeUnicode');
                }
                if (str_ends_with($lower, '_active')) {
                    $parts = explode('_', $f);
                    array_pop($parts);
                    $pdf->SetFont($ttfName ?: implode('_', $parts));
                    $activeFound = true;
                }
            }
            @closedir($dh);
        }

        if (!$activeFound) {
            \TCPDF_FONTS::addTTFfont(
                JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/tcpdf/fonts/verdana.ttf',
                'TrueTypeUnicode'
            );
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

    public function exportCsv(): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $ids = $input->get('cid', [], 'post', 'array');
        ArrayHelper::toInteger($ids);
        $formSelection = $input->getInt('form_selection', 0);

        $model = $this->getRecordModel();
        $config = $model->getExportConfig();
        $tz = $model->getTimezone();
        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);

        $delimiter = stripslashes((string) $config->csvdelimiter);
        $quote = stripslashes((string) $config->csvquote);
        $cellNewline = ((int) $config->cellnewline === 0) ? "\n" : "\\n";

        if ($ids) {
            $db->setQuery("Select * From #__facileforms_records Where id In (" . implode(',', $ids) . ") Order By submitted Desc");
            $recs = $db->loadObjectList();
            $formIds = array_unique(array_map(fn($r) => (int) $r->form, $recs));
            $db->setQuery("Select Distinct * From #__facileforms_elements Where form In (" . implode(',', $formIds) . ") And published = 1 And `name` Not In ('bfFakeName','bfFakeName2','bfFakeName3','bfFakeName4','bfFakeName5') Order By ordering");
        } elseif ($formSelection) {
            $db->setQuery("Select * From #__facileforms_records Where form = " . $formSelection . " Order By submitted Desc");
            $recs = $db->loadObjectList();
            $db->setQuery("Select Distinct * From #__facileforms_elements Where form = " . $formSelection . " And published = 1 And `name` Not In ('bfFakeName','bfFakeName2','bfFakeName3','bfFakeName4','bfFakeName5') Order By ordering");
        } else {
            $db->setQuery("Select * From #__facileforms_records Order By submitted Desc");
            $recs = $db->loadObjectList();
            $db->setQuery("Select Distinct * From #__facileforms_elements Where published = 1 And `name` Not In ('bfFakeName','bfFakeName2','bfFakeName3','bfFakeName4','bfFakeName5')");
        }
        $elementFields = $db->loadObjectList();
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
            $date = Factory::getDate($rec->submitted, $tz);
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

        @ob_end_clean();
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: private');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        echo "\xEF\xBB\xBF";
        echo $header . $body;
        $app->close();
    }

    public function exportXml(): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $ids = $input->get('cid', [], 'post', 'array');
        ArrayHelper::toInteger($ids);
        $formSelection = $input->getInt('form_selection', 0);

        $model = $this->getRecordModel();
        $tz = $model->getTimezone();
        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);

        if ($ids) {
            $db->setQuery("Select * From #__facileforms_records Where id In (" . implode(',', $ids) . ") Order By submitted Desc");
        } elseif ($formSelection) {
            $db->setQuery("Select * From #__facileforms_records Where form = " . $formSelection . " Order By submitted Desc");
        } else {
            $db->setQuery("Select * From #__facileforms_records Order By submitted Desc");
        }
        $recs = $db->loadObjectList();
        $formName = ($formSelection && $recs) ? ($recs[0]->name ?? '') : '';

        $datestamp = Factory::getDate('now', $tz);
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
            $date = Factory::getDate($rec->submitted, $tz);
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

        @ob_end_clean();
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: private');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        echo $xml;
        $app->close();
    }

    private function batchFlag(string $column): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $ids = $input->get('cid', [], 'post', 'array');
        ArrayHelper::toInteger($ids);
        $this->getRecordModel()->setFlagsBatch($ids, $column);
        $app->redirect($this->listUrl($input));
    }

    private function getRecordModel(): RecordModel
    {
        return Factory::getApplication()
            ->bootComponent('com_breezingformsng')
            ->getMVCFactory()
            ->createModel('Record', 'Administrator');
    }

    private function listUrl(\Joomla\CMS\Input\Input $input): string
    {
        $formSelection = $input->getInt('form_selection', 0);
        $searchTerm = $input->getString('searchterm', '');
        return 'index.php?option=com_breezingformsng&view=records'
            . ($formSelection > 0 ? '&form_selection=' . $formSelection : '')
            . ($searchTerm !== '' ? '&searchterm=' . rawurlencode($searchTerm) : '');
    }
}
