<?php

/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;

final class DatabaseRepairService
{
    public function __construct(
        private readonly DatabaseInterface $db,
        private readonly string $temporaryPath
    ) {
    }

    public static function getDuplicateIndexSelectionToken(array $group): string
    {
        $drop = array_values(array_map(
            static fn($value): string => trim((string) $value),
            (array) ($group['drop'] ?? [])
        ));

        return hash('sha256', implode("\0", [
            trim((string) ($group['table'] ?? '')),
            trim((string) ($group['keep'] ?? '')),
            implode("\0", $drop),
        ]));
    }

    /**
     * Repair only duplicate-index groups selected from a fresh audit report.
     *
     * @return array{
     *     selected_groups:int,
     *     repaired_groups:int,
     *     removed_indexes:int,
     *     failed_indexes:int,
     *     errors:array<int,string>
     * }
     */
    public function repairDuplicateIndexes(array $selectedTokens): array
    {
        $selectedTokens = array_values(array_unique(array_filter(
            array_map(static fn($value): string => trim((string) $value), $selectedTokens),
            static fn(string $value): bool => $value !== ''
        )));
        $report = (new DatabaseAuditService($this->db, $this->temporaryPath))->run();
        $groups = array_values((array) ($report['duplicate_indexes'] ?? []));
        $selectedGroups = [];
        $errors = [];
        $removedIndexes = 0;
        $failedIndexes = 0;
        $repairedGroups = 0;

        foreach ($groups as $group) {
            if (!is_array($group) || !in_array(self::getDuplicateIndexSelectionToken($group), $selectedTokens, true)) {
                continue;
            }

            $selectedGroups[] = $group;
            $tableAlias = trim((string) ($group['table'] ?? ''));
            $tableName = str_starts_with($tableAlias, '#__')
                ? $this->db->getPrefix() . substr($tableAlias, 3)
                : '';

            if ($tableName === '' || !in_array($tableName, $this->db->getTableList(), true)) {
                $failedIndexes += count((array) ($group['drop'] ?? []));
                $errors[] = $tableAlias !== '' ? $tableAlias : 'unknown table';
                continue;
            }

            $groupRemovedIndexes = 0;

            foreach ((array) ($group['drop'] ?? []) as $indexName) {
                $indexName = trim((string) $indexName);

                if ($indexName === '') {
                    continue;
                }

                try {
                    $this->db->setQuery(
                        'ALTER TABLE ' . $this->db->quoteName($tableName)
                        . ' DROP INDEX ' . $this->db->quoteName($indexName)
                    );
                    $this->db->execute();
                    $removedIndexes++;
                    $groupRemovedIndexes++;
                } catch (\Throwable $exception) {
                    $failedIndexes++;
                    $errors[] = $tableAlias . '/' . $indexName . ': ' . $exception->getMessage();
                }
            }

            if ($groupRemovedIndexes > 0) {
                $repairedGroups++;
            }
        }

        return [
            'selected_groups' => count($selectedGroups),
            'repaired_groups' => $repairedGroups,
            'removed_indexes' => $removedIndexes,
            'failed_indexes' => $failedIndexes,
            'errors' => $errors,
        ];
    }

    /**
     * Delete only stale installer directories selected from a fresh audit.
     *
     * @return array{selected_dirs:int, deleted_dirs:int, failed_dirs:int, errors:array<int,string>}
     */
    public function deleteStaleInstallerTemp(array $selectedPaths): array
    {
        $selectedPaths = array_values(array_unique(array_filter(
            array_map(static fn($value): string => trim((string) $value), $selectedPaths),
            static fn(string $value): bool => $value !== ''
        )));
        $report = (new DatabaseAuditService($this->db, $this->temporaryPath))->run();
        $stalePaths = array_values(array_filter(
            array_map(
                static fn($value): string => realpath((string) $value) ?: '',
                (array) ($report['stale_installer_temp_dirs'] ?? [])
            ),
            static fn(string $value): bool => $value !== ''
        ));
        $selectedDirs = array_values(array_intersect($selectedPaths, $stalePaths));
        $errors = [];
        $deletedDirs = 0;

        foreach ($selectedDirs as $path) {
            try {
                if (!is_dir($path) || !Folder::delete($path)) {
                    throw new \RuntimeException('The directory could not be removed.');
                }

                $deletedDirs++;
            } catch (\Throwable $exception) {
                $errors[] = $path . ': ' . $exception->getMessage();
            }
        }

        return [
            'selected_dirs' => count($selectedDirs),
            'deleted_dirs' => $deletedDirs,
            'failed_dirs' => count($errors),
            'errors' => $errors,
        ];
    }
}
