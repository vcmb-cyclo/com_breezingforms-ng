<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\Database\DatabaseInterface;

final class DatabaseAuditService
{
    private const EXPECTED_TABLES = [
        'facileforms_config',
        'facileforms_packages',
        'facileforms_compmenus',
        'facileforms_forms',
        'facileforms_elements',
        'facileforms_scripts',
        'facileforms_pieces',
        'facileforms_records',
        'facileforms_subrecords',
        'facileforms_integrator_criteria_fixed',
        'facileforms_integrator_criteria_form',
        'facileforms_integrator_criteria_joomla',
        'facileforms_integrator_items',
        'facileforms_integrator_rules',
    ];

    private const TARGET_COLLATION = 'utf8mb4_unicode_ci';

    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    public function run(): array
    {
        $tableList = $this->db->getTableList();
        $tables = [];
        $missingTables = [];
        $collationIssues = [];
        $duplicateIndexes = [];
        $totalRows = 0;

        foreach (self::EXPECTED_TABLES as $table) {
            $physicalTable = $this->db->getPrefix() . $table;
            $alias = '#__' . $table;

            if (!in_array($physicalTable, $tableList, true)) {
                $missingTables[] = $alias;
                continue;
            }

            $status = $this->getTableStatus($physicalTable);
            $rows = (int) ($status['TABLE_ROWS'] ?? 0);
            $collation = (string) ($status['TABLE_COLLATION'] ?? '');
            $totalRows += $rows;
            $tables[] = [
                'table' => $alias,
                'rows' => $rows,
                'engine' => (string) ($status['ENGINE'] ?? ''),
                'collation' => $collation,
                'size_bytes' => (int) ($status['DATA_LENGTH'] ?? 0) + (int) ($status['INDEX_LENGTH'] ?? 0),
            ];

            if (strcasecmp($collation, self::TARGET_COLLATION) !== 0) {
                $collationIssues[] = [
                    'table' => $alias,
                    'collation' => $collation,
                    'expected' => self::TARGET_COLLATION,
                ];
            }

            foreach ($this->findDuplicateIndexes($physicalTable, $alias) as $duplicate) {
                $duplicateIndexes[] = $duplicate;
            }
        }

        $orphanChecks = $this->findOrphans($tableList);
        $orphanRows = array_sum(array_column($orphanChecks, 'count'));
        $issuesTotal = count($missingTables) + count($collationIssues) + count($duplicateIndexes) + $orphanRows;

        return [
            'generated_at' => (new Date())->toSql(),
            'target_collation' => self::TARGET_COLLATION,
            'tables' => $tables,
            'missing_tables' => $missingTables,
            'collation_issues' => $collationIssues,
            'duplicate_indexes' => $duplicateIndexes,
            'orphan_checks' => $orphanChecks,
            'summary' => [
                'expected_tables' => count(self::EXPECTED_TABLES),
                'scanned_tables' => count($tables),
                'missing_tables' => count($missingTables),
                'total_rows' => $totalRows,
                'collation_issues' => count($collationIssues),
                'duplicate_index_groups' => count($duplicateIndexes),
                'orphan_rows' => $orphanRows,
                'issues_total' => $issuesTotal,
            ],
        ];
    }

    private function getTableStatus(string $physicalTable): array
    {
        $query = $this->db->getQuery(true)
            ->select(['TABLE_ROWS', 'ENGINE', 'TABLE_COLLATION', 'DATA_LENGTH', 'INDEX_LENGTH'])
            ->from($this->db->quoteName('information_schema.TABLES'))
            ->where('TABLE_SCHEMA = DATABASE()')
            ->where($this->db->quoteName('TABLE_NAME') . ' = :table')
            ->bind(':table', $physicalTable);
        $this->db->setQuery($query);

        return (array) ($this->db->loadAssoc() ?: []);
    }

    private function findDuplicateIndexes(string $physicalTable, string $alias): array
    {
        $this->db->setQuery('SHOW INDEX FROM ' . $this->db->quoteName($physicalTable));
        $rows = $this->db->loadAssocList() ?: [];
        $indexes = [];

        foreach ($rows as $row) {
            $name = (string) ($row['Key_name'] ?? '');
            if ($name === '' || strtoupper($name) === 'PRIMARY') {
                continue;
            }

            $sequence = (int) ($row['Seq_in_index'] ?? 0);
            $indexes[$name]['meta'] = implode(':', [
                (string) ($row['Non_unique'] ?? '1'),
                strtoupper((string) ($row['Index_type'] ?? 'BTREE')),
            ]);
            $indexes[$name][$sequence] = implode(':', [
                strtolower((string) ($row['Column_name'] ?? '')),
                (string) ($row['Sub_part'] ?? ''),
                strtoupper((string) ($row['Collation'] ?? 'A')),
            ]);
        }

        $bySignature = [];
        foreach ($indexes as $name => $definition) {
            $meta = (string) ($definition['meta'] ?? '1:BTREE');
            unset($definition['meta']);
            $columns = $definition;
            ksort($columns, SORT_NUMERIC);
            $bySignature[$meta . '|' . implode(',', $columns)][] = $name;
        }

        $duplicates = [];
        foreach ($bySignature as $names) {
            if (count($names) < 2) {
                continue;
            }

            sort($names, SORT_NATURAL | SORT_FLAG_CASE);
            $duplicates[] = ['table' => $alias, 'indexes' => $names];
        }

        return $duplicates;
    }

    private function findOrphans(array $tableList): array
    {
        $checks = [
            'records_without_form' => ['facileforms_records', 'facileforms_forms', 'child.form = parent.id'],
            'elements_without_form' => ['facileforms_elements', 'facileforms_forms', 'child.form = parent.id'],
            'subrecords_without_record' => ['facileforms_subrecords', 'facileforms_records', 'child.record = parent.id'],
            'subrecords_without_element' => ['facileforms_subrecords', 'facileforms_elements', 'child.element = parent.id'],
        ];
        $result = [];

        foreach ($checks as $id => [$child, $parent, $join]) {
            if (!in_array($this->db->getPrefix() . $child, $tableList, true)
                || !in_array($this->db->getPrefix() . $parent, $tableList, true)) {
                continue;
            }

            $sql = 'SELECT COUNT(*) FROM ' . $this->db->quoteName('#__' . $child) . ' AS child'
                . ' LEFT JOIN ' . $this->db->quoteName('#__' . $parent) . ' AS parent ON ' . $join
                . ' WHERE parent.id IS NULL';
            $this->db->setQuery($sql);
            $result[] = ['id' => $id, 'count' => (int) $this->db->loadResult()];
        }

        return $result;
    }
}
