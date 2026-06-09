<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

abstract class LegacyPackageModel extends ListModel
{
    public static function create(): static
    {
        $model = new static([
            'filter_fields' => [
                'a.description',
                'a.id',
                'a.modified',
                'a.name',
                'a.package',
                'a.published',
                'a.title',
                'a.type',
            ],
            'ignore_request' => true,
            'option' => 'com_breezingformsng',
        ]);

        $model->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));

        return $model;
    }

    public function deleteByIds(array $ids): int
    {
        $ids = $this->filterIds($ids);

        if ($ids === []) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName($this->getTableName()));

        $this->bindIdList($query, $ids);

        $db->setQuery($query)->execute();

        return count($ids);
    }

    public function publishByIds(array $ids, bool $published): int
    {
        $ids = $this->filterIds($ids);

        if ($ids === []) {
            return 0;
        }

        $db = $this->getDatabase();
        $publishedValue = $published ? 1 : 0;
        $query = $db->getQuery(true)
            ->update($db->quoteName($this->getTableName()))
            ->set($db->quoteName('published') . ' = :published');

        $query->bind(':published', $publishedValue, ParameterType::INTEGER);
        $this->bindIdList($query, $ids);

        $db->setQuery($query)->execute();

        return count($ids);
    }

    public function packageExists(string $package): bool
    {
        if ($package === '') {
            return true;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($this->getTableName()))
            ->where($db->quoteName('package') . ' = :package');

        $query->bind(':package', $package);

        return (int) $db->setQuery($query)->loadResult() > 0;
    }

    public function getPackages(): array
    {
        $db = $this->getDatabase();
        $emptyPackage = '';
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('package', 'name'))
            ->from($db->quoteName($this->getTableName()))
            ->where($db->quoteName('package') . ' IS NOT NULL')
            ->where($db->quoteName('package') . ' != :emptyPackage')
            ->order($db->quoteName('name'));

        $query->bind(':emptyPackage', $emptyPackage);

        return $db->setQuery($query)->loadObjectList();
    }

    public function getListData(
        string $package,
        string $search,
        string $sort,
        string $direction,
        int $limit,
        int $limitStart,
        bool $includeInternal = true,
        string $filterState = ''
    ): array {
        $this->setState('filter.package', $package);
        $this->setState('filter.search', $search);
        $this->setState('filter.include_internal', $includeInternal);
        $this->setState('filter.state', $filterState);
        $this->setState('list.ordering', $this->normaliseSortField($sort));
        $this->setState('list.direction', strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC');
        $this->setState('list.limit', max(0, $limit));
        $this->setState('list.start', max(0, $limitStart));
        $this->setState('list.links', 0);

        return [
            'rows' => $this->getItems(),
            'total' => $this->getTotal(),
            'limitstart' => $this->getStart(),
            'pagination' => $this->getPagination(),
        ];
    }

    protected function getListQuery(): QueryInterface
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('a.*')
            ->from($db->quoteName($this->getTableName(), 'a'));

        $this->applyListFilters($query);

        $ordering = (string) $this->getState('list.ordering', 'a.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $query->order($db->quoteName($this->normaliseSortField($ordering)) . ' ' . $direction . ', ' . $db->quoteName('a.id') . ' DESC');

        return $query;
    }

    abstract protected function getTableName(): string;

    private function applyListFilters(QueryInterface $query): void
    {
        $db = $this->getDatabase();
        $package = (string) $this->getState('filter.package', '');
        $search = (string) $this->getState('filter.search', '');
        $includeInternal = (bool) $this->getState('filter.include_internal', true);
        $state = (string) $this->getState('filter.state', '');

        if ($package !== '') {
            $query->where($db->quoteName('a.package') . ' = :package');
            $query->bind(':package', $package);
        }

        if (!$includeInternal) {
            $internalPrefix = '\\_%';
            $query->where($db->quoteName('a.name') . ' NOT LIKE :internalPrefix');
            $query->bind(':internalPrefix', $internalPrefix);
        }

        if ($state === 'P') {
            $publishedVal = 1;
            $query->where($db->quoteName('a.published') . ' = :published');
            $query->bind(':published', $publishedVal, \Joomla\Database\ParameterType::INTEGER);
        } elseif ($state === 'U') {
            $publishedVal = 0;
            $query->where($db->quoteName('a.published') . ' = :published');
            $query->bind(':published', $publishedVal, \Joomla\Database\ParameterType::INTEGER);
        }

        if ($search !== '') {
            $searchLike = '%' . $search . '%';
            $query->where(
                '(' .
                $db->quoteName('a.title') . ' LIKE :searchTitle OR ' .
                $db->quoteName('a.name') . ' LIKE :searchName OR ' .
                $db->quoteName('a.description') . ' LIKE :searchDescription' .
                ')'
            );
            $query->bind(':searchTitle', $searchLike);
            $query->bind(':searchName', $searchLike);
            $query->bind(':searchDescription', $searchLike);
        }
    }

    private function filterIds(array $ids): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map('intval', $ids),
                    static fn (int $id): bool => $id > 0
                )
            )
        );
    }

    private function bindIdList(QueryInterface $query, array $ids): void
    {
        $db = $this->getDatabase();
        $placeholders = [];

        foreach ($ids as $index => $id) {
            $placeholder = ':id' . $index;
            $placeholders[] = $placeholder;
            $query->bind($placeholder, $ids[$index], ParameterType::INTEGER);
        }

        $query->where($db->quoteName('id') . ' IN (' . implode(',', $placeholders) . ')');
    }

    private function normaliseSortField(string $sort): string
    {
        $allowedSorts = [
            'description' => 'a.description',
            'id' => 'a.id',
            'modified' => 'a.modified',
            'name' => 'a.name',
            'package' => 'a.package',
            'published' => 'a.published',
            'title' => 'a.title',
            'type' => 'a.type',
            'a.description' => 'a.description',
            'a.id' => 'a.id',
            'a.modified' => 'a.modified',
            'a.name' => 'a.name',
            'a.package' => 'a.package',
            'a.published' => 'a.published',
            'a.title' => 'a.title',
            'a.type' => 'a.type',
        ];

        return $allowedSorts[$sort] ?? 'a.name';
    }
}
