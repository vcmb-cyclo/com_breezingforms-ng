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

    private const FALLBACK_COLLATION = 'utf8mb4_unicode_ci';

    private ?string $resolvedTargetCollation = null;

    public function __construct(
        private readonly DatabaseInterface $db,
        private readonly string $temporaryPath
    ) {
    }

    public function run(): array
    {
        $targetCollation = $this->resolveTargetCollation();
        $tableList = $this->db->getTableList();
        $tables = [];
        $missingTables = [];
        $collationIssues = [];
        $columnCollationIssues = [];
        $duplicateIndexes = [];
        $totalRows = 0;
        $totalDataBytes = 0;
        $totalIndexBytes = 0;

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
            $dataBytes = (int) ($status['DATA_LENGTH'] ?? 0);
            $indexBytes = (int) ($status['INDEX_LENGTH'] ?? 0);
            $totalRows += $rows;
            $totalDataBytes += $dataBytes;
            $totalIndexBytes += $indexBytes;
            $tables[] = [
                'table' => $alias,
                'rows' => $rows,
                'engine' => (string) ($status['ENGINE'] ?? ''),
                'collation' => $collation,
                'size_bytes' => $dataBytes + $indexBytes,
            ];

            if (strcasecmp($collation, $targetCollation) !== 0) {
                $collationIssues[] = [
                    'table' => $alias,
                    'collation' => $collation,
                    'expected' => $targetCollation,
                ];
            }

            foreach ($this->findColumnCollationIssues($physicalTable, $alias, $targetCollation) as $columnIssue) {
                $columnCollationIssues[] = $columnIssue;
            }

            foreach ($this->findDuplicateIndexes($physicalTable, $alias) as $duplicate) {
                $duplicateIndexes[] = $duplicate;
            }
        }

        $collationHistogram = $this->buildCollationHistogram($tables);
        $orphanChecks = $this->findOrphans($tableList);
        $orphanRows = array_sum(array_column($orphanChecks, 'count'));
        $unexpectedTables = $this->findUnexpectedTables($tableList);
        $staleLanguageFiles = $this->findStaleLanguageFiles();
        $staleInstallerTempDirs = $this->findStaleInstallerTempDirectories();
        $menuIssues = $this->findMenuIssues();
        $extensionIssues = $this->findExtensionIssues();
        $duplicateForms = $this->findDuplicateForms();
        $issuesTotal = count($missingTables) + count($collationIssues) + count($columnCollationIssues)
            + count($duplicateIndexes) + $orphanRows + count($unexpectedTables)
            + count($staleLanguageFiles) + count($staleInstallerTempDirs)
            + count($menuIssues) + count($extensionIssues['duplicates']) + count($extensionIssues['legacy'])
            + count($duplicateForms);

        return [
            'generated_at' => (new Date())->toSql(),
            'target_collation' => $targetCollation,
            'tables' => $tables,
            'missing_tables' => $missingTables,
            'unexpected_tables' => $unexpectedTables,
            'collation_issues' => $collationIssues,
            'column_collation_issues' => $columnCollationIssues,
            'collation_histogram' => $collationHistogram,
            'duplicate_indexes' => $duplicateIndexes,
            'orphan_checks' => $orphanChecks,
            'stale_language_files' => $staleLanguageFiles,
            'stale_installer_temp_dirs' => $staleInstallerTempDirs,
            'menu_issues' => $menuIssues,
            'extension_duplicates' => $extensionIssues['duplicates'],
            'extension_legacy' => $extensionIssues['legacy'],
            'duplicate_forms' => $duplicateForms,
            'summary' => [
                'expected_tables' => count(self::EXPECTED_TABLES),
                'scanned_tables' => count($tables),
                'missing_tables' => count($missingTables),
                'unexpected_tables' => count($unexpectedTables),
                'total_rows' => $totalRows,
                'total_data_bytes' => $totalDataBytes,
                'total_index_bytes' => $totalIndexBytes,
                'collation_issues' => count($collationIssues),
                'column_collation_issues' => count($columnCollationIssues),
                'mixed_collations' => count($collationHistogram) > 1,
                'duplicate_index_groups' => count($duplicateIndexes),
                'orphan_rows' => $orphanRows,
                'stale_language_files' => count($staleLanguageFiles),
                'stale_installer_temp_dirs' => count($staleInstallerTempDirs),
                'menu_issues' => count($menuIssues),
                'extension_duplicates' => count($extensionIssues['duplicates']),
                'extension_legacy' => count($extensionIssues['legacy']),
                'duplicate_forms' => count($duplicateForms),
                'issues_total' => $issuesTotal,
            ],
        ];
    }

    /**
     * Prefer the server's modern utf8mb4 collation (e.g. utf8mb4_0900_ai_ci on
     * MySQL 8) over a hardcoded one, so tables that are already correctly
     * configured for this server aren't perpetually flagged as mismatched.
     */
    private function resolveTargetCollation(): string
    {
        if ($this->resolvedTargetCollation !== null) {
            return $this->resolvedTargetCollation;
        }

        $preferred = ['utf8mb4_0900_ai_ci', 'utf8mb4_unicode_520_ci', self::FALLBACK_COLLATION];

        foreach ($preferred as $candidate) {
            if ($this->isCollationSupported($candidate)) {
                return $this->resolvedTargetCollation = $candidate;
            }
        }

        return $this->resolvedTargetCollation = self::FALLBACK_COLLATION;
    }

    private function isCollationSupported(string $collation): bool
    {
        try {
            $query = $this->db->getQuery(true)
                ->select('COLLATION_NAME')
                ->from($this->db->quoteName('information_schema.COLLATIONS'))
                ->where($this->db->quoteName('COLLATION_NAME') . ' = :collation')
                ->bind(':collation', $collation);
            $this->db->setQuery($query);

            return (bool) $this->db->loadResult();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Catches columns that stayed on a stale collation even after the table's
     * own default was fixed (ALTER TABLE ... CONVERT TO CHARACTER SET only
     * changes new/altered columns going forward on some MySQL versions when
     * a column had an explicit COLLATE override).
     */
    private function findColumnCollationIssues(string $physicalTable, string $alias, string $targetCollation): array
    {
        $query = $this->db->getQuery(true)
            ->select(['COLUMN_NAME', 'COLLATION_NAME'])
            ->from($this->db->quoteName('information_schema.COLUMNS'))
            ->where('TABLE_SCHEMA = DATABASE()')
            ->where($this->db->quoteName('TABLE_NAME') . ' = :table')
            ->where($this->db->quoteName('COLLATION_NAME') . ' IS NOT NULL')
            ->bind(':table', $physicalTable);
        $this->db->setQuery($query);
        $columns = $this->db->loadAssocList() ?: [];

        $issues = [];
        foreach ($columns as $column) {
            $collation = (string) ($column['COLLATION_NAME'] ?? '');
            if ($collation !== '' && strcasecmp($collation, $targetCollation) !== 0) {
                $issues[] = [
                    'table' => $alias,
                    'column' => (string) ($column['COLUMN_NAME'] ?? ''),
                    'collation' => $collation,
                    'expected' => $targetCollation,
                ];
            }
        }

        return $issues;
    }

    private function buildCollationHistogram(array $tables): array
    {
        $histogram = [];
        foreach ($tables as $table) {
            $collation = (string) ($table['collation'] ?? '');
            if ($collation === '') {
                continue;
            }
            $histogram[$collation] = ($histogram[$collation] ?? 0) + 1;
        }
        arsort($histogram);

        return $histogram;
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
            $duplicates[] = [
                'table' => $alias,
                'indexes' => $names,
                'keep' => $names[0],
                'drop' => array_slice($names, 1),
            ];
        }

        return $duplicates;
    }

    private function findOrphans(array $tableList): array
    {
        $checks = [
            'records_without_form' => ['facileforms_records', 'facileforms_forms', 'child.form = parent.id'],
            'elements_without_form' => ['facileforms_elements', 'facileforms_forms', 'child.form = parent.id'],
            'subrecords_without_record' => [
                'facileforms_subrecords',
                'facileforms_records',
                'child.record = parent.id',
            ],
            'subrecords_without_element' => [
                'facileforms_subrecords',
                'facileforms_elements',
                'child.element = parent.id',
            ],
        ];
        $result = [];

        foreach ($checks as $id => [$child, $parent, $join]) {
            if (
                !in_array($this->db->getPrefix() . $child, $tableList, true)
                || !in_array($this->db->getPrefix() . $parent, $tableList, true)
            ) {
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

    /**
     * Flags any facileforms_ or breezingforms_ table on this prefix that
     * isn't in EXPECTED_TABLES - leftovers from an old install, a removed
     * feature, or a botched uninstall that a fresh EXPECTED_TABLES scan
     * alone would never surface (it only looks for tables it expects).
     */
    private function findUnexpectedTables(array $tableList): array
    {
        $prefix = $this->db->getPrefix();
        $expectedPhysical = array_map(
            static fn (string $table): string => $prefix . $table,
            self::EXPECTED_TABLES
        );

        $unexpected = [];
        foreach ($tableList as $physicalTable) {
            if (in_array($physicalTable, $expectedPhysical, true)) {
                continue;
            }

            $withoutPrefix = str_starts_with($physicalTable, $prefix)
                ? substr($physicalTable, strlen($prefix))
                : $physicalTable;

            if (preg_match('/^(facileforms|breezingforms)_?/i', $withoutPrefix) === 1) {
                $unexpected[] = '#__' . $withoutPrefix;
            }
        }

        sort($unexpected, SORT_NATURAL | SORT_FLAG_CASE);

        return $unexpected;
    }

    /**
     * Scans both the admin and site language folders for leftover files
     * from this component's older naming (com_breezingforms, no "ng"),
     * which a straightforward Joomla update never removes on its own.
     */
    private function findStaleLanguageFiles(): array
    {
        $stale = [];
        $roots = [
            JPATH_ADMINISTRATOR . '/language',
            JPATH_SITE . '/language',
        ];

        foreach ($roots as $root) {
            if (!is_dir($root)) {
                continue;
            }

            $matches = glob($root . '/*/com_breezingforms.*') ?: [];
            foreach ($matches as $match) {
                $stale[] = str_replace(JPATH_ROOT, '', $match);
            }
        }

        sort($stale, SORT_NATURAL | SORT_FLAG_CASE);

        return $stale;
    }

    /**
     * Leftover administrator/components/com_breezingformsng-named temp
     * folders under Joomla's configured tmp_path, from an install/update
     * that was interrupted before cleanup.
     */
    private function findStaleInstallerTempDirectories(): array
    {
        $tmpPath = $this->temporaryPath;

        if (!is_dir($tmpPath)) {
            return [];
        }

        $matches = glob($tmpPath . '/install_*', GLOB_ONLYDIR) ?: [];
        $stale = [];

        foreach ($matches as $match) {
            // Joomla names these folders with a random suffix, not the
            // extension name, so check the extracted contents instead of
            // the folder name itself.
            $manifestMatches = glob($match . '/*com_breezingformsng*.xml') ?: [];
            if ($manifestMatches === []) {
                continue;
            }

            $stale[] = str_replace(JPATH_ROOT, '', $match);
        }

        sort($stale, SORT_NATURAL | SORT_FLAG_CASE);

        return $stale;
    }

    /**
     * Site menu items pointing at this component whose target form (referenced
     * by name via the ff_com_name menu parameter, not by id) is missing,
     * unpublished, or not configured at all - plus menus still linking to the
     * pre-NG option=com_breezingforms component.
     */
    private function findMenuIssues(): array
    {
        $issues = [];

        try {
            $query = $this->db->getQuery(true)
                ->select($this->db->quoteName(['id', 'title', 'link', 'published', 'params']))
                ->from($this->db->quoteName('#__menu'))
                ->where($this->db->quoteName('client_id') . ' = 0')
                ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
                ->where($this->db->quoteName('link') . ' LIKE ' . $this->db->quote('%option=com_breezingforms%'))
                ->order($this->db->quoteName('id') . ' ASC');
            $this->db->setQuery($query);
            $menus = $this->db->loadAssocList() ?: [];

            $query = $this->db->getQuery(true)
                ->select($this->db->quoteName(['name', 'published']))
                ->from($this->db->quoteName('#__facileforms_forms'));
            $this->db->setQuery($query);
            $formRows = $this->db->loadAssocList() ?: [];
        } catch (\Throwable) {
            return [];
        }

        $publishedByName = [];
        foreach ($formRows as $formRow) {
            $name = trim((string) ($formRow['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $publishedByName[$name] = ($publishedByName[$name] ?? false) || (int) ($formRow['published'] ?? 0) === 1;
        }

        foreach ($menus as $menu) {
            $link = trim((string) ($menu['link'] ?? ''));
            $linkQuery = [];
            parse_str((string) parse_url($link, PHP_URL_QUERY), $linkQuery);
            $option = (string) ($linkQuery['option'] ?? '');
            $menuIssues = [];
            $formName = '';

            if ($option === 'com_breezingforms') {
                $menuIssues[] = 'legacy_component_link';
            } elseif ($option === 'com_breezingformsng') {
                $params = json_decode((string) ($menu['params'] ?? ''), true);
                $formName = trim((string) (is_array($params) ? ($params['ff_com_name'] ?? '') : ''));

                if ($formName === '') {
                    $menuIssues[] = 'no_form_configured';
                } elseif (!array_key_exists($formName, $publishedByName)) {
                    $menuIssues[] = 'form_missing';
                } elseif (!$publishedByName[$formName]) {
                    $menuIssues[] = 'form_unpublished';
                }
            }

            if ($menuIssues !== []) {
                $issues[] = [
                    'menu_id' => (int) ($menu['id'] ?? 0),
                    'title' => trim((string) ($menu['title'] ?? '')),
                    'published' => (int) ($menu['published'] ?? 0),
                    'link' => $link,
                    'form_name' => $formName,
                    'issues' => $menuIssues,
                ];
            }
        }

        return $issues;
    }

    /**
     * Duplicate #__extensions registrations (same type/element/folder/client)
     * left behind by repeated discover-installs, and leftover rows from the
     * pre-NG crosstec extensions that an update never removes.
     */
    private function findExtensionIssues(): array
    {
        try {
            $query = $this->db->getQuery(true)
                ->select($this->db->quoteName([
                    'extension_id',
                    'name',
                    'type',
                    'element',
                    'folder',
                    'client_id',
                    'enabled',
                ]))
                ->from($this->db->quoteName('#__extensions'))
                ->where($this->db->quoteName('element') . ' LIKE ' . $this->db->quote('%breezingforms%'))
                ->order($this->db->quoteName('extension_id') . ' ASC');
            $this->db->setQuery($query);
            $rows = $this->db->loadAssocList() ?: [];
        } catch (\Throwable) {
            return ['duplicates' => [], 'legacy' => []];
        }

        $byKey = [];
        $legacy = [];

        foreach ($rows as $row) {
            $element = strtolower(trim((string) ($row['element'] ?? '')));
            $entry = [
                'extension_id' => (int) ($row['extension_id'] ?? 0),
                'name' => (string) ($row['name'] ?? ''),
                'type' => (string) ($row['type'] ?? ''),
                'element' => (string) ($row['element'] ?? ''),
                'folder' => (string) ($row['folder'] ?? ''),
                'enabled' => (int) ($row['enabled'] ?? 0),
            ];

            // The NG component and its compatibility plugin are the expected rows.
            $isExpected = ($entry['type'] === 'component' && $element === 'com_breezingformsng')
                || ($entry['type'] === 'plugin' && $element === 'bfcompat' && $entry['folder'] === 'system');

            if (!$isExpected && !str_contains($element, 'breezingformsng')) {
                $legacy[] = $entry;
            }

            $key = implode('|', [
                $entry['type'],
                $element,
                strtolower($entry['folder']),
                (int) ($row['client_id'] ?? 0),
            ]);
            $byKey[$key][] = $entry;
        }

        $duplicates = [];
        foreach ($byKey as $entries) {
            if (count($entries) > 1) {
                // Keep the first registration; every later row is redundant.
                $duplicates[] = [
                    'keep' => $entries[0],
                    'drop' => array_slice($entries, 1),
                ];
            }
        }

        return ['duplicates' => $duplicates, 'legacy' => $legacy];
    }

    /**
     * Forms sharing the same name within the same package - typically leftovers
     * from a package being imported several times. Genuinely harmful here
     * because site menu items reference forms by name (ff_com_name), so
     * duplicated names make that resolution ambiguous. The row to keep is the
     * one holding submitted records (or the oldest when none has any).
     */
    private function findDuplicateForms(): array
    {
        try {
            $query = $this->db->getQuery(true)
                ->select($this->db->quoteName(['f.id', 'f.name', 'f.package', 'f.published']))
                ->select('COUNT(' . $this->db->quoteName('r.id') . ') AS ' . $this->db->quoteName('record_count'))
                ->from($this->db->quoteName('#__facileforms_forms', 'f'))
                ->join('LEFT', $this->db->quoteName('#__facileforms_records', 'r'), 'r.form = f.id')
                ->group($this->db->quoteName('f.id'))
                ->order($this->db->quoteName('f.id') . ' ASC');
            $this->db->setQuery($query);
            $rows = $this->db->loadAssocList() ?: [];
        } catch (\Throwable) {
            return [];
        }

        $byKey = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $byKey[$name . '|' . trim((string) ($row['package'] ?? ''))][] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => $name,
                'package' => trim((string) ($row['package'] ?? '')),
                'published' => (int) ($row['published'] ?? 0),
                'record_count' => (int) ($row['record_count'] ?? 0),
            ];
        }

        $duplicates = [];
        foreach ($byKey as $entries) {
            if (count($entries) < 2) {
                continue;
            }

            // Prefer the row that actually holds records; ties go to the oldest id.
            usort($entries, static fn(array $a, array $b): int =>
                [$b['record_count'], $a['id']] <=> [$a['record_count'], $b['id']]);

            $duplicates[] = [
                'name' => $entries[0]['name'],
                'package' => $entries[0]['package'],
                'keep' => $entries[0],
                'drop' => array_slice($entries, 1),
            ];
        }

        return $duplicates;
    }
}
