<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Event;

class RecordModel extends BaseModel
{
    private \DateTimeZone $tz;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->tz = new \DateTimeZone(Factory::getApplication()->get('offset'));
    }

    public function getTimezone(): \DateTimeZone
    {
        return $this->tz;
    }

    public function getRecord(int $id): ?\stdClass
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery(
            'Select records.*, forms.title As form_title, forms.name As form_name'
            . ' From #__facileforms_records As records'
            . ' Inner Join #__facileforms_forms As forms On forms.id = records.form'
            . ' Where records.id = ' . $id
        );
        return $db->loadObject() ?: null;
    }

    public function getEditableElements(int $formId): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery(
            "Select id, title, name, type"
            . " From #__facileforms_elements"
            . " Where published = 1"
            . " And `name` Not In ('bfFakeName','bfFakeName2','bfFakeName3','bfFakeName4','bfFakeName5')"
            . " And form = " . $formId
            . " Order By ordering"
        );
        return $db->loadAssocList();
    }

    public function getEditableRows(int $recordId, int $formId, string $recordName): array
    {
        $elements = $this->getEditableElements($formId);
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery(
            'Select id, record, element, title, name, type, value'
            . ' From #__facileforms_subrecords'
            . ' Where record = ' . $recordId
            . ' Order By id'
        );
        $subrecords = $db->loadAssocList();

        $byElement = [];
        $byName = [];
        foreach ($subrecords as $sub) {
            $byElement[(int) $sub['element']][] = $sub;
            $byName[(string) $sub['name']][] = $sub;
        }

        $rows = [];
        foreach ($elements as $element) {
            $elementId = (int) $element['id'];
            $name = (string) $element['name'];
            $matches = $byElement[$elementId] ?? $byName[$name] ?? [];
            $values = array_map(fn($m) => (string) $m['value'], $matches);

            if (!count($values) && $name === 'Formulaire') {
                $values[] = $recordName;
            }

            $rows[] = [
                'element_id' => $elementId,
                'title'      => (string) $element['title'],
                'name'       => $name,
                'type'       => (string) $element['type'],
                'value'      => implode("\n", $values),
            ];
        }

        return $rows;
    }

    public function saveRecord(int $recordId, array $values): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery('Select form From #__facileforms_records Where id = ' . $recordId);
        $formId = (int) $db->loadResult();

        if ($formId < 1) {
            return;
        }

        foreach ($this->getEditableElements($formId) as $element) {
            $elementId = (int) $element['id'];
            if (!array_key_exists($elementId, $values)) {
                continue;
            }
            $this->saveElementValue($recordId, $element, (string) $values[$elementId]);
        }

    }

    private function saveElementValue(int $recordId, array $element, string $value): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $elementId = (int) $element['id'];
        $name = (string) $element['name'];

        $db->setQuery(
            'Select id From #__facileforms_subrecords'
            . ' Where record = ' . $recordId
            . ' And (element = ' . $elementId . ' Or name = ' . $db->quote($name) . ')'
            . ' Order By id'
        );
        $subrecordIds = array_map('intval', $db->loadColumn());
        $values = $this->splitValue($value, (string) $element['type']) ?: [''];

        foreach ($subrecordIds as $index => $subrecordId) {
            if (!array_key_exists($index, $values)) {
                break;
            }
            $db->setQuery(
                'Update #__facileforms_subrecords'
                . ' Set element = ' . $elementId
                . ', title = ' . $db->quote((string) $element['title'])
                . ', name = ' . $db->quote($name)
                . ', type = ' . $db->quote((string) $element['type'])
                . ', value = ' . $db->quote($values[$index])
                . ' Where id = ' . $subrecordId
                . ' And record = ' . $recordId
            );
            $db->execute();
        }

        for ($i = count($subrecordIds); $i < count($values); $i++) {
            if ($values[$i] === '') {
                continue;
            }
            $db->setQuery(
                'Insert Into #__facileforms_subrecords (record, element, title, name, type, value)'
                . ' Values (' . $recordId . ', ' . $elementId
                . ', ' . $db->quote((string) $element['title'])
                . ', ' . $db->quote($name)
                . ', ' . $db->quote((string) $element['type'])
                . ', ' . $db->quote($values[$i]) . ')'
            );
            $db->execute();
        }
    }

    private function splitValue(string $value, string $type): array
    {
        $value = str_replace("\r", '', $value);
        if (!in_array($type, ['Checkbox', 'Checkbox Group', 'Select List'], true)) {
            return [$value];
        }
        return array_values(array_map(
            'trim',
            str_contains($value, "\n") ? explode("\n", $value) : explode(', ', $value)
        ));
    }

    public function deleteRecords(array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $idsSql = implode(',', $ids);

        if (file_exists(JPATH_SITE . '/administrator/components/com_contentbuilderng/com_contentbuilderng.xml')) {
            $db->setQuery(
                "Select `form`.id As form_id, `form`.reference_id, `form`.delete_articles, r.id As record_id"
                . " From #__facileforms_records As r"
                . " Inner Join #__contentbuilderng_forms As form On form.reference_id = r.form"
                . " Where r.id In (" . $idsSql . ")"
            );
            foreach ($db->loadAssocList() as $cbRecord) {
                $db->setQuery("Delete From #__contentbuilderng_list_records Where form_id = " . (int) $cbRecord['form_id'] . " And record_id = " . (int) $cbRecord['record_id']);
                $db->execute();

                $db->setQuery("Delete From #__contentbuilderng_records Where `type` = 'com_breezingformsng' And `reference_id` = " . $db->quote($cbRecord['reference_id']) . " And record_id = " . (int) $cbRecord['record_id']);
                $db->execute();

                if ((int) $cbRecord['delete_articles'] === 1) {
                    $db->setQuery("Select article_id From #__contentbuilderng_articles Where form_id = " . (int) $cbRecord['form_id'] . " And record_id = " . (int) $cbRecord['record_id']);
                    foreach ($db->loadColumn() as $article) {
                        $table = Table::getInstance('content');
                        if ($table->load((int) $article)) {
                            Factory::getApplication()->getDispatcher()->dispatch('onContentBeforeDelete', new Event('onContentBeforeDelete', ['com_content.article', $table]));
                        }
                        $db->setQuery("Delete From #__content Where id = " . (int) $article);
                        $db->execute();
                        $table->reset();
                        Factory::getApplication()->getDispatcher()->dispatch('onContentAfterDelete', new Event('onContentAfterDelete', ['com_content.article', $table]));
                        $db->setQuery("Delete From #__assets Where `name` = " . $db->quote('com_content.article.' . (int) $article));
                        $db->execute();
                    }
                }

                $db->setQuery("Delete From #__contentbuilderng_articles Where form_id = " . (int) $cbRecord['form_id'] . " And record_id = " . (int) $cbRecord['record_id']);
                $db->execute();
            }
        }

        $db->setQuery("Delete From #__facileforms_subrecords Where record In (" . $idsSql . ")");
        $db->execute();
        $db->setQuery("Delete From #__facileforms_records Where id In (" . $idsSql . ")");
        $db->execute();
    }

    public function setFlagsBatch(array $ids, string $column): void
    {
        if (!in_array($column, ['viewed', 'exported', 'archived'], true)) {
            return;
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return;
        }
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Update #__facileforms_records Set `" . $column . "` = 1 Where id In (" . implode(',', $ids) . ")");
        $db->execute();
    }

    public function setFlagSingle(int $recordId, string $column, int $value): void
    {
        $col = preg_replace('/^bfrecord_/', '', $column);
        if (!in_array($col, ['viewed', 'exported', 'archived'], true)) {
            return;
        }
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Update #__facileforms_records Set `" . $col . "` = " . $value . " Where id = " . $recordId);
        $db->execute();
    }

    public function importCsv(int $formId, string $file, string $encoding = '0'): int
    {
        if ($formId < 1 || !is_file($file) || !is_readable($file)) {
            return 0;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName(['title', 'name']))
                ->from($db->quoteName('#__facileforms_forms'))
                ->where($db->quoteName('id') . ' = ' . $formId)
        );
        $form = $db->loadObject();

        if ($form === null) {
            return 0;
        }

        $handle = fopen($file, 'rb');

        if ($handle === false) {
            return 0;
        }

        if ($encoding !== '0') {
            $content = stream_get_contents($handle);
            fclose($handle);
            $converted = iconv($encoding, 'UTF-8//TRANSLIT', $content === false ? '' : $content);

            if ($converted === false) {
                throw new \RuntimeException('Unable to convert CSV input to UTF-8.');
            }

            $handle = fopen('php://temp', 'w+b');
            fwrite($handle, $converted);
            rewind($handle);
        }

        $config = $this->getExportConfig();
        $delimiter = stripslashes((string) $config->csvdelimiter);
        $enclosure = stripslashes((string) $config->csvquote);
        $delimiter = strlen($delimiter) === 1 ? $delimiter : ';';
        $enclosure = strlen($enclosure) === 1 ? $enclosure : '"';
        $header = fgetcsv($handle, null, $delimiter, $enclosure, '');

        if (!is_array($header) || count($header) < 2) {
            fclose($handle);
            return 0;
        }

        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        $header = array_map(static fn ($value): string => trim((string) $value), $header);
        $normalizedHeader = array_map('strtolower', $header);
        $elements = $this->getEditableElements($formId);
        $elementsByName = [];

        foreach ($elements as $element) {
            $elementsByName[(string) $element['name']] = $element;
            $elementsByName[strtolower((string) $element['name'])] = $element;
        }

        $fixedHeaders = [
            'id', 'submitted', 'form', 'name', 'bf_form_name', 'title', 'bf_form_title',
            'ip', 'browser', 'opsys', 'provider', 'viewed', 'exported', 'archived',
            'user_id', 'username', 'user_full_name', 'paypal_tx_id', 'paypal_payment_date',
            'paypal_testaccount', 'paypal_download_tries', 'double_opt_in', 'opted',
        ];
        $identity = Factory::getApplication()->getIdentity();
        $valueAt = static function (array $row, array $keys, string $key, mixed $default = ''): mixed {
            $index = array_search($key, $keys, true);
            return $index === false ? $default : ($row[$index] ?? $default);
        };
        $decodeCell = static fn (mixed $value): string => (int) $config->cellnewline === 0
            ? (string) $value
            : str_replace('\\n', "\n", (string) $value);
        $imported = 0;
        $db->transactionStart();

        try {
            while (($row = fgetcsv($handle, null, $delimiter, $enclosure, '')) !== false) {
                if ($row === [null] || !array_filter($row, static fn ($value): bool => trim((string) $value) !== '')) {
                    continue;
                }

                $submitted = trim((string) $valueAt($row, $normalizedHeader, 'submitted', ''));
                $paymentDate = trim((string) $valueAt($row, $normalizedHeader, 'paypal_payment_date', ''));
                $record = (object) [
                    'submitted' => preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $submitted)
                        ? $submitted
                        : (new \Joomla\CMS\Date\Date())->toSql(),
                    'form' => $formId,
                    'title' => $decodeCell($valueAt($row, $normalizedHeader, 'bf_form_title', $form->title)),
                    'name' => (string) $form->name,
                    'ip' => (string) $valueAt($row, $normalizedHeader, 'ip', ''),
                    'browser' => $decodeCell($valueAt($row, $normalizedHeader, 'browser', '')),
                    'opsys' => (string) $valueAt($row, $normalizedHeader, 'opsys', ''),
                    'provider' => (string) $valueAt($row, $normalizedHeader, 'provider', ''),
                    'viewed' => (int) (bool) $valueAt($row, $normalizedHeader, 'viewed', 0),
                    'exported' => (int) (bool) $valueAt($row, $normalizedHeader, 'exported', 0),
                    'archived' => (int) (bool) $valueAt($row, $normalizedHeader, 'archived', 0),
                    'user_id' => (int) $valueAt($row, $normalizedHeader, 'user_id', $identity->id),
                    'username' => (string) $valueAt($row, $normalizedHeader, 'username', $identity->username),
                    'user_full_name' => (string) $valueAt($row, $normalizedHeader, 'user_full_name', $identity->name),
                    'paypal_tx_id' => (string) $valueAt($row, $normalizedHeader, 'paypal_tx_id', ''),
                    'paypal_payment_date' => preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $paymentDate)
                        ? $paymentDate
                        : '1970-01-01 00:00:00',
                    'paypal_testaccount' => (int) (bool) $valueAt($row, $normalizedHeader, 'paypal_testaccount', 0),
                    'paypal_download_tries' => (int) $valueAt($row, $normalizedHeader, 'paypal_download_tries', 0),
                    'opted' => (int) (bool) $valueAt(
                        $row,
                        $normalizedHeader,
                        'double_opt_in',
                        $valueAt($row, $normalizedHeader, 'opted', 0)
                    ),
                ];
                $db->insertObject('#__facileforms_records', $record, 'id');
                $recordId = (int) $record->id;

                foreach ($header as $index => $fieldName) {
                    if (in_array($normalizedHeader[$index], $fixedHeaders, true)) {
                        continue;
                    }

                    $element = $elementsByName[$fieldName] ?? $elementsByName[strtolower($fieldName)] ?? null;

                    if ($element === null) {
                        continue;
                    }

                    $value = $decodeCell($row[$index] ?? '');
                    if (in_array((string) $element['type'], ['Checkbox', 'Checkbox Group', 'Select List'], true)) {
                        $value = str_replace('|', "\n", $value);
                    }
                    $this->saveElementValue($recordId, $element, $value);
                }

                $imported++;
            }

            $db->transactionCommit();
        } catch (\Throwable $exception) {
            $db->transactionRollback();
            throw $exception;
        } finally {
            fclose($handle);
        }

        return $imported;
    }

    public function getExportConfig(): \stdClass
    {
        $params = ComponentHelper::getParams('com_breezingformsng');

        $config = new \stdClass();
        $config->csvdelimiter = $params->get('csvdelimiter', ';');
        $config->csvquote     = $params->get('csvquote', '"');
        $config->cellnewline  = (int) $params->get('cellnewline', 1);
        $config->csvinverted  = false;

        return $config;
    }

    public function getSubrecords(int $recordId): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery(
            "Select Distinct subs.* From #__facileforms_subrecords As subs, #__facileforms_elements As els"
            . " Where els.id = subs.element And subs.record = " . $recordId
            . " Order By els.ordering"
        );
        return $db->loadObjectList();
    }

    public function markExported(array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return;
        }
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Update #__facileforms_records Set exported = 1 Where id In (" . implode(',', $ids) . ")");
        $db->execute();
    }
}
