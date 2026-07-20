<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class ScriptModel extends PackageModel
{
    public function prepareList(string $package): array
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $session = $app->getSession();
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
            $search = (string) $session->get('bf.scripts_search', '');
        } else {
            $search = trim($input->getString('search', ''));
            $session->set('bf.scripts_search', $search);
        }

        $filterOrderInput = trim($input->getString('filter_order', ''));
        $filterOrderDirInput = strtoupper(trim($input->getString('filter_order_Dir', '')));

        if ($filterOrderInput !== '') {
            $sort = $filterOrderInput;
            $session->set('bf.scripts_sort', $sort);
        } elseif (!$input->exists('sort')) {
            $sort = (string) $session->get('bf.scripts_sort', 'a.name');
        } else {
            $sort = $input->getCmd('sort', 'a.name');
            $session->set('bf.scripts_sort', $sort);
        }

        if ($filterOrderDirInput !== '') {
            $direction = $filterOrderDirInput === 'DESC' ? 'DESC' : 'ASC';
            $session->set('bf.scripts_dir', $direction);
        } elseif (!$input->exists('dir')) {
            $direction = strtoupper((string) $session->get('bf.scripts_dir', 'ASC'));
        } else {
            $direction = strtoupper($input->getCmd('dir', 'ASC'));
            $session->set('bf.scripts_dir', $direction);
        }

        $direction = $direction === 'DESC' ? 'DESC' : 'ASC';

        $filterStateInput = strtoupper(trim($input->getString('filter_state', '')));
        if ($input->exists('filter_state')) {
            $filterState = in_array($filterStateInput, ['P', 'U'], true) ? $filterStateInput : '';
            $session->set('bf.scripts_filter_state', $filterState);
        } else {
            $filterState = (string) $session->get('bf.scripts_filter_state', '');
        }

        $pageSizes = [0, 5, 10, 15, 20, 25, 30, 50, 100, 200, 500];
        $list = (array) $input->get('list', [], 'array');
        $limitRequest = isset($list['limit']) ? (int) $list['limit'] : $input->getInt('limit', -1);

        if ($limitRequest >= 0 && in_array($limitRequest, $pageSizes, true)) {
            $limit = $limitRequest;
            $session->set('bf.scripts_limit', $limit);
        } else {
            $limit = (int) $session->get('bf.scripts_limit', 10);

            if (!in_array($limit, $pageSizes, true)) {
                $limit = 10;
            }
        }

        $limitStartRequest = isset($list['start']) ? (int) $list['start'] : $input->getInt('limitstart', -1);
        $limitStart = $limitStartRequest >= 0 ? $limitStartRequest : (int) $session->get('bf.scripts_limitstart', 0);
        $limitStart = max(0, $limitStart);

        $listData = $this->getListData($package, $search, $sort, $direction, $limit, $limitStart, $filterState);
        $session->set('bf.scripts_limitstart', $listData['limitstart']);

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

    protected function getTableName(): string
    {
        return '#__facileforms_scripts';
    }
}
