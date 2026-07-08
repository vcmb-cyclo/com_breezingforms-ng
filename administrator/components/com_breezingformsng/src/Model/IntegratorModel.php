<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Source history: admin/integrator.class.php (git mv — Phase 3).
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Database\DatabaseInterface;

class IntegratorModel extends BaseModel
{
    private const ALLOWED_OPERATORS = ['=', '<>', '>', '<', '>=', '<=', '%...%', '%...', '...%'];

    private function db(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    public function getRules(): array
    {
        $db = $this->db();
        $db->setQuery(
            'SELECT rules.*, rules.id AS id,'
            . " CONCAT('" . $db->getPrefix() . "', rules.reference_table) AS reference_table,"
            . ' forms.name AS form_name, forms.id AS form_id'
            . ' FROM #__facileforms_integrator_rules AS rules'
            . ' JOIN #__facileforms_forms AS forms ON rules.form_id = forms.id'
            . ' GROUP BY rules.id ORDER BY rules.id'
        );
        return $db->loadObjectList() ?: [];
    }

    public function getRule(int $id): ?\stdClass
    {
        $db = $this->db();
        $db->setQuery(
            'SELECT rules.*, rules.id AS id,'
            . " CONCAT('" . $db->getPrefix() . "', rules.reference_table) AS reference_table,"
            . ' forms.name AS form_name, forms.id AS form_id'
            . ' FROM #__facileforms_integrator_rules AS rules'
            . ' JOIN #__facileforms_forms AS forms ON rules.form_id = forms.id'
            . ' WHERE rules.id = ' . $id
            . ' GROUP BY rules.id'
        );
        $rows = $db->loadObjectList();
        return count($rows) === 1 ? $rows[0] : null;
    }

    public function getItems(int $ruleId): array
    {
        $db = $this->db();
        $db->setQuery(
            'SELECT items.*, elements.name AS element_name, elements.type AS element_type'
            . ' FROM #__facileforms_integrator_items AS items'
            . ' JOIN #__facileforms_elements AS elements ON elements.id = items.element_id'
            . ' WHERE items.rule_id = ' . $ruleId
            . ' GROUP BY items.id ORDER BY items.id DESC'
        );
        return $db->loadObjectList() ?: [];
    }

    public function getTableNames(): array
    {
        return $this->db()->getTableList();
    }

    public function getTableColumns(string $table): array
    {
        try {
            return $this->db()->getTableColumns($table) ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function getForms(string $filter = 'all'): array
    {
        $db    = $this->db();
        $where = match ($filter) {
            'published'   => ' WHERE forms.published = 1',
            'unpublished' => ' WHERE forms.published = 0',
            default       => '',
        };
        $db->setQuery('SELECT id, name, published FROM #__facileforms_forms AS forms' . $where);
        return $db->loadObjectList() ?: [];
    }

    public function getFormElements(int $formId): array
    {
        $db = $this->db();
        $db->setQuery('SELECT id, name, type FROM #__facileforms_elements WHERE form = ' . $formId);
        return $db->loadObjectList() ?: [];
    }

    public function saveRule(string $name, int $formId, string $referenceTable, string $type): int
    {
        $db  = $this->db();
        $pfx = $db->getPrefix();
        $tbl = str_starts_with($referenceTable, $pfx)
            ? substr($referenceTable, \strlen($pfx))
            : $referenceTable;

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__facileforms_integrator_rules'))
            ->columns($db->quoteName(['name', 'form_id', 'reference_table', 'type']))
            ->values(
                $db->quote($name) . ','
                . (int) $formId . ','
                . $db->quote($tbl) . ','
                . $db->quote(\in_array($type, ['insert', 'update'], true) ? $type : 'insert')
            );
        $db->setQuery($query)->execute();
        return (int) $db->insertid();
    }

    public function deleteRules(array $ids): void
    {
        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return;
        }
        $db = $this->db();
        $in = implode(',', $ids);
        foreach ([
            ['#__facileforms_integrator_rules', 'id'],
            ['#__facileforms_integrator_items', 'rule_id'],
            ['#__facileforms_integrator_criteria_form', 'rule_id'],
            ['#__facileforms_integrator_criteria_joomla', 'rule_id'],
            ['#__facileforms_integrator_criteria_fixed', 'rule_id'],
        ] as [$tbl, $col]) {
            $db->setQuery("DELETE FROM {$tbl} WHERE {$col} IN ({$in})")->execute();
        }
    }

    public function publishRule(int $id, int $state): void
    {
        $this->db()->setQuery(
            'UPDATE #__facileforms_integrator_rules SET published = ' . ($state ? 1 : 0) . ' WHERE id = ' . $id
        )->execute();
    }

    public function getCriteria(int $ruleId): array
    {
        $db = $this->db();
        $db->setQuery(
            'SELECT crit.*, elements.name AS element_name, elements.type AS element_type'
            . ' FROM #__facileforms_integrator_criteria_form AS crit'
            . ' JOIN #__facileforms_elements AS elements ON elements.id = crit.element_id'
            . ' WHERE crit.rule_id = ' . $ruleId
            . ' GROUP BY crit.id ORDER BY crit.id DESC'
        );
        return $db->loadObjectList() ?: [];
    }

    public function addCriteria(int $ruleId, string $operator, string $referenceColumn, int $elementId, string $andor): void
    {
        if (!\in_array($operator, self::ALLOWED_OPERATORS, true)) {
            return;
        }
        $db    = $this->db();
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__facileforms_integrator_criteria_form'))
            ->columns($db->quoteName(['rule_id', 'operator', 'reference_column', 'element_id', 'andor']))
            ->values(
                $ruleId . ','
                . $db->quote($operator) . ','
                . $db->quote($referenceColumn) . ','
                . $elementId . ','
                . $db->quote(\in_array($andor, ['AND', 'OR'], true) ? $andor : 'AND')
            );
        $db->setQuery($query)->execute();
    }

    public function removeCriteria(int $id): void
    {
        $this->db()->setQuery(
            'DELETE FROM #__facileforms_integrator_criteria_form WHERE id = ' . $id
        )->execute();
    }

    public function getCriteriaJoomla(int $ruleId): array
    {
        $db = $this->db();
        $db->setQuery(
            'SELECT crit.* FROM #__facileforms_integrator_criteria_joomla AS crit'
            . ' WHERE crit.rule_id = ' . $ruleId
            . ' GROUP BY crit.id ORDER BY crit.id DESC'
        );
        return $db->loadObjectList() ?: [];
    }

    public function addCriteriaJoomla(int $ruleId, string $operator, string $referenceColumn, string $joomlaObject, string $andor): void
    {
        if (!\in_array($operator, self::ALLOWED_OPERATORS, true)) {
            return;
        }
        $allowed = ['Userid', 'Username', 'Language', 'Date'];
        $db      = $this->db();
        $query   = $db->getQuery(true)
            ->insert($db->quoteName('#__facileforms_integrator_criteria_joomla'))
            ->columns($db->quoteName(['rule_id', 'operator', 'reference_column', 'joomla_object', 'andor']))
            ->values(
                $ruleId . ','
                . $db->quote($operator) . ','
                . $db->quote($referenceColumn) . ','
                . $db->quote(\in_array($joomlaObject, $allowed, true) ? $joomlaObject : 'Userid') . ','
                . $db->quote(\in_array($andor, ['AND', 'OR'], true) ? $andor : 'AND')
            );
        $db->setQuery($query)->execute();
    }

    public function removeCriteriaJoomla(int $id): void
    {
        $this->db()->setQuery(
            'DELETE FROM #__facileforms_integrator_criteria_joomla WHERE id = ' . $id
        )->execute();
    }

    public function getCriteriaFixed(int $ruleId): array
    {
        $db = $this->db();
        $db->setQuery(
            'SELECT crit.* FROM #__facileforms_integrator_criteria_fixed AS crit'
            . ' WHERE crit.rule_id = ' . $ruleId
            . ' GROUP BY crit.id ORDER BY crit.id DESC'
        );
        return $db->loadObjectList() ?: [];
    }

    public function addCriteriaFixed(int $ruleId, string $operator, string $referenceColumn, string $fixedValue, string $andor): void
    {
        if (!\in_array($operator, self::ALLOWED_OPERATORS, true)) {
            return;
        }
        $db    = $this->db();
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__facileforms_integrator_criteria_fixed'))
            ->columns($db->quoteName(['rule_id', 'operator', 'reference_column', 'fixed_value', 'andor']))
            ->values(
                $ruleId . ','
                . $db->quote($operator) . ','
                . $db->quote($referenceColumn) . ','
                . $db->quote($fixedValue) . ','
                . $db->quote(\in_array($andor, ['AND', 'OR'], true) ? $andor : 'AND')
            );
        $db->setQuery($query)->execute();
    }

    public function removeCriteriaFixed(int $id): void
    {
        $this->db()->setQuery(
            'DELETE FROM #__facileforms_integrator_criteria_fixed WHERE id = ' . $id
        )->execute();
    }

    public function addItem(int $ruleId, int $elementId, string $referenceColumn): void
    {
        $db    = $this->db();
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__facileforms_integrator_items'))
            ->columns($db->quoteName(['rule_id', 'element_id', 'reference_column']))
            ->values($ruleId . ',' . $elementId . ',' . $db->quote($referenceColumn));
        $db->setQuery($query)->execute();
    }

    public function removeItem(int $id): void
    {
        $this->db()->setQuery(
            'DELETE FROM #__facileforms_integrator_items WHERE id = ' . $id
        )->execute();
    }

    public function saveCode(int $itemId, int $ruleId, string $code): void
    {
        $db    = $this->db();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__facileforms_integrator_items'))
            ->set($db->quoteName('code') . ' = ' . $db->quote($code))
            ->where($db->quoteName('id') . ' = ' . $itemId)
            ->where($db->quoteName('rule_id') . ' = ' . $ruleId);
        $db->setQuery($query)->execute();
    }

    public function saveFinalizeCode(int $ruleId, string $code): void
    {
        $db = $this->db();
        $db->setQuery(
            'UPDATE #__facileforms_integrator_rules SET finalize_code = '
            . $db->quote($code) . ' WHERE id = ' . $ruleId
        )->execute();
    }

    public function publishItem(int $id, int $state): void
    {
        $this->db()->setQuery(
            'UPDATE #__facileforms_integrator_items SET published = ' . ($state ? 1 : 0) . ' WHERE id = ' . $id
        )->execute();
    }
}
