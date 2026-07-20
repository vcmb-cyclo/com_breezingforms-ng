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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\Event;

class RecordModel extends BaseDatabaseModel
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
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['records.*', 'forms.title AS form_title', 'forms.name AS form_name'])
            ->from($db->quoteName('#__facileforms_records', 'records'))
            ->join('INNER', $db->quoteName('#__facileforms_forms', 'forms') . ' ON forms.id = records.form')
            ->where('records.id = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        return $db->loadObject() ?: null;
    }

    public function getEditableElements(int $formId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['id', 'title', 'name', 'type'])
            ->from($db->quoteName('#__facileforms_elements'))
            ->where($db->quoteName('published') . ' = 1')
            ->whereNotIn($db->quoteName('name'), ['bfFakeName', 'bfFakeName2', 'bfFakeName3', 'bfFakeName4', 'bfFakeName5'], ParameterType::STRING)
            ->where($db->quoteName('form') . ' = :formId')
            ->order($db->quoteName('ordering'))
            ->bind(':formId', $formId, ParameterType::INTEGER);
        $db->setQuery($query);
        return $db->loadAssocList();
    }

    public function getEditableRows(int $recordId, int $formId, string $recordName): array
    {
        $elements = $this->getEditableElements($formId);
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['id', 'record', 'element', 'title', 'name', 'type', 'value'])
            ->from($db->quoteName('#__facileforms_subrecords'))
            ->where($db->quoteName('record') . ' = :recordId')
            ->order($db->quoteName('id'))
            ->bind(':recordId', $recordId, ParameterType::INTEGER);
        $db->setQuery($query);
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
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('form'))
            ->from($db->quoteName('#__facileforms_records'))
            ->where($db->quoteName('id') . ' = :recordId')
            ->bind(':recordId', $recordId, ParameterType::INTEGER);
        $db->setQuery($query);
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

        $user = Factory::getApplication()->getIdentity();
        $now  = (new \Joomla\CMS\Date\Date('now', $this->tz))->format('Y-m-d H:i:s', true);
        $username = (string) $user->username;
        $userId = (int) $user->id;
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__facileforms_records'))
            ->set($db->quoteName('modified') . ' = :now')
            ->set($db->quoteName('modified_by') . ' = :username')
            ->set($db->quoteName('modified_user_id') . ' = :userId')
            ->where($db->quoteName('id') . ' = :recordId')
            ->bind(':now', $now, ParameterType::STRING)
            ->bind(':username', $username, ParameterType::STRING)
            ->bind(':userId', $userId, ParameterType::INTEGER)
            ->bind(':recordId', $recordId, ParameterType::INTEGER);
        $db->setQuery($query)->execute();
    }

    private function saveElementValue(int $recordId, array $element, string $value): void
    {
        $db = $this->getDatabase();
        $elementId = (int) $element['id'];
        $name = (string) $element['name'];
        $title = (string) $element['title'];
        $type = (string) $element['type'];

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__facileforms_subrecords'))
            ->where($db->quoteName('record') . ' = :recordId')
            ->extendWhere('AND', [
                $db->quoteName('element') . ' = :elementId',
                $db->quoteName('name') . ' = :name',
            ], 'OR')
            ->order($db->quoteName('id'))
            ->bind(':recordId', $recordId, ParameterType::INTEGER)
            ->bind(':elementId', $elementId, ParameterType::INTEGER)
            ->bind(':name', $name, ParameterType::STRING);
        $db->setQuery($query);
        $subrecordIds = array_map('intval', $db->loadColumn());
        $values = $this->splitValue($value, $type) ?: [''];

        foreach ($subrecordIds as $index => $subrecordId) {
            if (!array_key_exists($index, $values)) {
                break;
            }
            $rowValue = $values[$index];
            $updateQuery = $db->getQuery(true)
                ->update($db->quoteName('#__facileforms_subrecords'))
                ->set($db->quoteName('element') . ' = :elementId')
                ->set($db->quoteName('title') . ' = :title')
                ->set($db->quoteName('name') . ' = :name')
                ->set($db->quoteName('type') . ' = :type')
                ->set($db->quoteName('value') . ' = :value')
                ->where($db->quoteName('id') . ' = :subrecordId')
                ->where($db->quoteName('record') . ' = :recordId')
                ->bind(':elementId', $elementId, ParameterType::INTEGER)
                ->bind(':title', $title, ParameterType::STRING)
                ->bind(':name', $name, ParameterType::STRING)
                ->bind(':type', $type, ParameterType::STRING)
                ->bind(':value', $rowValue, ParameterType::STRING)
                ->bind(':subrecordId', $subrecordId, ParameterType::INTEGER)
                ->bind(':recordId', $recordId, ParameterType::INTEGER);
            $db->setQuery($updateQuery)->execute();
        }

        for ($i = count($subrecordIds); $i < count($values); $i++) {
            if ($values[$i] === '') {
                continue;
            }
            $rowValue = $values[$i];
            $insertQuery = $db->getQuery(true)
                ->insert($db->quoteName('#__facileforms_subrecords'))
                ->columns($db->quoteName(['record', 'element', 'title', 'name', 'type', 'value']))
                ->values(':recordId, :elementId, :title, :name, :type, :value')
                ->bind(':recordId', $recordId, ParameterType::INTEGER)
                ->bind(':elementId', $elementId, ParameterType::INTEGER)
                ->bind(':title', $title, ParameterType::STRING)
                ->bind(':name', $name, ParameterType::STRING)
                ->bind(':type', $type, ParameterType::STRING)
                ->bind(':value', $rowValue, ParameterType::STRING);
            $db->setQuery($insertQuery)->execute();
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

        $db = $this->getDatabase();

        if (file_exists(JPATH_SITE . '/administrator/components/com_contentbuilderng/com_contentbuilderng.xml')) {
            $query = $db->getQuery(true)
                ->select(['form.id AS form_id', 'form.reference_id', 'form.delete_articles', 'r.id AS record_id'])
                ->from($db->quoteName('#__facileforms_records', 'r'))
                ->join('INNER', $db->quoteName('#__contentbuilderng_forms', 'form') . ' ON form.reference_id = r.form')
                ->whereIn('r.id', $ids, ParameterType::INTEGER);
            $db->setQuery($query);

            foreach ($db->loadAssocList() as $cbRecord) {
                $formId = (int) $cbRecord['form_id'];
                $recordId = (int) $cbRecord['record_id'];
                $referenceId = (string) $cbRecord['reference_id'];

                $delListRecords = $db->getQuery(true)
                    ->delete($db->quoteName('#__contentbuilderng_list_records'))
                    ->where($db->quoteName('form_id') . ' = :formId')
                    ->where($db->quoteName('record_id') . ' = :recordId')
                    ->bind(':formId', $formId, ParameterType::INTEGER)
                    ->bind(':recordId', $recordId, ParameterType::INTEGER);
                $db->setQuery($delListRecords)->execute();

                $delCbRecords = $db->getQuery(true)
                    ->delete($db->quoteName('#__contentbuilderng_records'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('com_breezingformsng'))
                    ->where($db->quoteName('reference_id') . ' = :referenceId')
                    ->where($db->quoteName('record_id') . ' = :recordId')
                    ->bind(':referenceId', $referenceId, ParameterType::STRING)
                    ->bind(':recordId', $recordId, ParameterType::INTEGER);
                $db->setQuery($delCbRecords)->execute();

                if ((int) $cbRecord['delete_articles'] === 1) {
                    $contentFactory = Factory::getApplication()
                        ->bootComponent('com_content')
                        ->getMVCFactory();
                    $articleQuery = $db->getQuery(true)
                        ->select($db->quoteName('article_id'))
                        ->from($db->quoteName('#__contentbuilderng_articles'))
                        ->where($db->quoteName('form_id') . ' = :formId')
                        ->where($db->quoteName('record_id') . ' = :recordId')
                        ->bind(':formId', $formId, ParameterType::INTEGER)
                        ->bind(':recordId', $recordId, ParameterType::INTEGER);
                    $db->setQuery($articleQuery);

                    foreach ($db->loadColumn() as $article) {
                        $articleId = (int) $article;
                        $table = $contentFactory->createTable('Article', 'Administrator');
                        if ($table->load($articleId)) {
                            Factory::getApplication()->getDispatcher()->dispatch('onContentBeforeDelete', new Event('onContentBeforeDelete', ['com_content.article', $table]));
                        }

                        $delArticle = $db->getQuery(true)
                            ->delete($db->quoteName('#__content'))
                            ->where($db->quoteName('id') . ' = :articleId')
                            ->bind(':articleId', $articleId, ParameterType::INTEGER);
                        $db->setQuery($delArticle)->execute();

                        $table->reset();
                        Factory::getApplication()->getDispatcher()->dispatch('onContentAfterDelete', new Event('onContentAfterDelete', ['com_content.article', $table]));

                        $assetName = 'com_content.article.' . $articleId;
                        $delAsset = $db->getQuery(true)
                            ->delete($db->quoteName('#__assets'))
                            ->where($db->quoteName('name') . ' = :assetName')
                            ->bind(':assetName', $assetName, ParameterType::STRING);
                        $db->setQuery($delAsset)->execute();
                    }
                }

                $delCbArticles = $db->getQuery(true)
                    ->delete($db->quoteName('#__contentbuilderng_articles'))
                    ->where($db->quoteName('form_id') . ' = :formId')
                    ->where($db->quoteName('record_id') . ' = :recordId')
                    ->bind(':formId', $formId, ParameterType::INTEGER)
                    ->bind(':recordId', $recordId, ParameterType::INTEGER);
                $db->setQuery($delCbArticles)->execute();
            }
        }

        $delSubrecords = $db->getQuery(true)
            ->delete($db->quoteName('#__facileforms_subrecords'))
            ->whereIn($db->quoteName('record'), $ids, ParameterType::INTEGER);
        $db->setQuery($delSubrecords)->execute();

        $delRecords = $db->getQuery(true)
            ->delete($db->quoteName('#__facileforms_records'))
            ->whereIn($db->quoteName('id'), $ids, ParameterType::INTEGER);
        $db->setQuery($delRecords)->execute();
    }

    public function setFlagsBatch(array $ids, string $column, int $value = 1): void
    {
        if (!in_array($column, ['viewed', 'exported', 'archived'], true)) {
            return;
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return;
        }
        $db = $this->getDatabase();
        $flag = $value ? 1 : 0;
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__facileforms_records'))
            ->set($db->quoteName($column) . ' = :flag')
            ->whereIn($db->quoteName('id'), $ids, ParameterType::INTEGER)
            ->bind(':flag', $flag, ParameterType::INTEGER);
        $db->setQuery($query)->execute();
    }

    public function setFlagSingle(int $recordId, string $column, int $value): void
    {
        $col = preg_replace('/^bfrecord_/', '', $column);
        if (!in_array($col, ['viewed', 'exported', 'archived'], true)) {
            return;
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__facileforms_records'))
            ->set($db->quoteName($col) . ' = :value')
            ->where($db->quoteName('id') . ' = :recordId')
            ->bind(':value', $value, ParameterType::INTEGER)
            ->bind(':recordId', $recordId, ParameterType::INTEGER);
        $db->setQuery($query)->execute();
    }

    public function importCsv(int $formId, string $file, string $encoding = '0'): int
    {
        if ($formId < 1 || !is_file($file) || !is_readable($file)) {
            return 0;
        }

        $db = $this->getDatabase();
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
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('subs') . '.*')
            ->from($db->quoteName('#__facileforms_subrecords', 'subs'))
            ->from($db->quoteName('#__facileforms_elements', 'els'))
            ->where('els.id = subs.element')
            ->where('subs.record = :recordId')
            ->order('els.ordering')
            ->bind(':recordId', $recordId, ParameterType::INTEGER);
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function markExported(array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return;
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__facileforms_records'))
            ->set($db->quoteName('exported') . ' = 1')
            ->whereIn($db->quoteName('id'), $ids, ParameterType::INTEGER);
        $db->setQuery($query)->execute();
    }
}
