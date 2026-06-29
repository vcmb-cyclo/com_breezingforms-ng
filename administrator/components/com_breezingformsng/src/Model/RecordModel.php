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

        $userId = (int) Factory::getApplication()->getIdentity()->id;
        $db->setQuery(
            'Update #__facileforms_records'
            . ' Set modified = ' . $db->quote(Factory::getDate()->toSql())
            . ', modified_by = ' . $userId
            . ', modified_user_id = ' . $userId
            . ' Where id = ' . $recordId
        );
        $db->execute();
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
