<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Input\Input;
use Joomla\Session\SessionInterface;

abstract class PackageModel extends ListModel
{
    public function prepareList(string $package, Input $input, SessionInterface $session): array
    {
        $prefix = $this->getSessionPrefix();
        $packages = $this->getPackages();

        $packageOk = $package === '';

        foreach ($packages as $packageEntry) {
            if ((string) $packageEntry->name === $package) {
                $packageOk = true;
                break;
            }
        }

        if (!$packageOk) {
            $package = '';
        }

        $packageList = [[
            $package === '',
            '',
        ]];

        foreach ($packages as $packageEntry) {
            $packageName = (string) $packageEntry->name;
            $packageList[] = [
                $packageName === $package,
                $packageName,
            ];
        }

        if (!$input->exists('search')) {
            $search = (string) $session->get('bf.' . $prefix . '_search', '');
        } else {
            $search = trim($input->getString('search', ''));
            $session->set('bf.' . $prefix . '_search', $search);
        }

        $filterOrderInput = trim($input->getString('filter_order', ''));
        $filterOrderDirInput = strtoupper(trim($input->getString('filter_order_Dir', '')));

        if ($filterOrderInput !== '') {
            $sort = $filterOrderInput;
            $session->set('bf.' . $prefix . '_sort', $sort);
        } elseif (!$input->exists('sort')) {
            $sort = (string) $session->get('bf.' . $prefix . '_sort', 'a.name');
        } else {
            $sort = $input->getCmd('sort', 'a.name');
            $session->set('bf.' . $prefix . '_sort', $sort);
        }

        if ($filterOrderDirInput !== '') {
            $direction = $filterOrderDirInput === 'DESC' ? 'DESC' : 'ASC';
            $session->set('bf.' . $prefix . '_dir', $direction);
        } elseif (!$input->exists('dir')) {
            $direction = strtoupper((string) $session->get('bf.' . $prefix . '_dir', 'ASC'));
        } else {
            $direction = strtoupper($input->getCmd('dir', 'ASC'));
            $session->set('bf.' . $prefix . '_dir', $direction);
        }

        $direction = $direction === 'DESC' ? 'DESC' : 'ASC';

        $filterStateInput = strtoupper(trim($input->getString('filter_state', '')));
        if ($input->exists('filter_state')) {
            $filterState = in_array($filterStateInput, ['P', 'U'], true) ? $filterStateInput : '';
            $session->set('bf.' . $prefix . '_filter_state', $filterState);
        } else {
            $filterState = (string) $session->get('bf.' . $prefix . '_filter_state', '');
        }

        $pageSizes = [0, 5, 10, 15, 20, 25, 30, 50, 100, 200, 500];
        $list = (array) $input->get('list', [], 'array');
        $limitRequest = isset($list['limit']) ? (int) $list['limit'] : $input->getInt('limit', -1);

        if ($limitRequest >= 0 && in_array($limitRequest, $pageSizes, true)) {
            $limit = $limitRequest;
            $session->set('bf.' . $prefix . '_limit', $limit);
        } else {
            $limit = (int) $session->get('bf.' . $prefix . '_limit', 10);

            if (!in_array($limit, $pageSizes, true)) {
                $limit = 10;
            }
        }

        $limitStartRequest = isset($list['start']) ? (int) $list['start'] : $input->getInt('limitstart', -1);
        $limitStart = $limitStartRequest >= 0 ? $limitStartRequest : (int) $session->get('bf.' . $prefix . '_limitstart', 0);
        $limitStart = max(0, $limitStart);

        $listData = $this->getListData($package, $search, $sort, $direction, $limit, $limitStart, $filterState);
        $session->set('bf.' . $prefix . '_limitstart', $listData['limitstart']);

        $listOrder = (string) $this->getState('list.ordering', 'a.name');
        $listDirn = strtolower((string) $this->getState('list.direction', 'asc'));

        return [
            'package' => $package,
            'packageList' => $packageList,
            'search' => $search,
            'total' => $listData['total'],
            'limit' => $limit,
            'limitStart' => $listData['limitstart'],
            'rows' => $listData['rows'],
            'pagination' => $listData['pagination'],
            'listOrder' => $listOrder,
            'listDirn' => $listDirn,
            'filterState' => $filterState,
        ];
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
        string $filterState = ''
    ): array {
        // Initialise Joomla's ListModel state before applying the explicit
        // request values, otherwise populateState() overwrites the requested
        // ordering when getItems() first reads the state.
        $this->getState();

        $this->setState('filter.package', $package);
        $this->setState('filter.search', $search);
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

    abstract protected function getSessionPrefix(): string;

    private function applyListFilters(QueryInterface $query): void
    {
        $db = $this->getDatabase();
        $package = (string) $this->getState('filter.package', '');
        $search = (string) $this->getState('filter.search', '');
        $state = (string) $this->getState('filter.state', '');

        if ($package !== '') {
            $query->where($db->quoteName('a.package') . ' = :package');
            $query->bind(':package', $package);
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
