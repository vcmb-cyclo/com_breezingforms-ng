<?php
/**
 * @package     BreezingForms NG
 * @copyright   Copyright (C) 2024-2026 by XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class PieceModel extends LegacyPackageModel
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

        if (!$input->exists('show_internal')) {
            $showInternal = (int) $session->get('bf.show_internal_pieces', 0);
        } else {
            $showInternal = $input->getInt('show_internal', 0);
            $session->set('bf.show_internal_pieces', $showInternal);
        }

        if (!$input->exists('search')) {
            $search = (string) $session->get('bf.pieces_search', '');
        } else {
            $search = trim($input->getString('search', ''));
            $session->set('bf.pieces_search', $search);
        }

        if (!$input->exists('sort')) {
            $sort = (string) $session->get('bf.pieces_sort', 'name');
        } else {
            $sort = $input->getCmd('sort', 'name');
            $session->set('bf.pieces_sort', $sort);
        }

        if (!$input->exists('dir')) {
            $direction = strtoupper((string) $session->get('bf.pieces_dir', 'ASC'));
        } else {
            $direction = strtoupper($input->getCmd('dir', 'ASC'));
            $session->set('bf.pieces_dir', $direction);
        }

        $direction = $direction === 'DESC' ? 'DESC' : 'ASC';
        $pageSizes = [10, 25, 50, 100, 250, 500, 1000, 5000, 10000, 100000];
        $limitRequest = $input->getInt('limit', -1);

        if ($limitRequest > 0 && in_array($limitRequest, $pageSizes, true)) {
            $limit = $limitRequest;
            $session->set('bf.pieces_limit', $limit);
        } else {
            $limit = (int) $session->get('bf.pieces_limit', 10);

            if (!in_array($limit, $pageSizes, true)) {
                $limit = 10;
            }
        }

        $limitStartRequest = $input->getInt('limitstart', -1);
        $limitStart = $limitStartRequest >= 0 ? $limitStartRequest : (int) $session->get('bf.pieces_limitstart', 0);
        $limitStart = max(0, $limitStart);

        $listData = $this->getListData($package, $search, $sort, $direction, $limit, $limitStart, (bool) $showInternal);
        $session->set('bf.pieces_limitstart', $listData['limitstart']);

        return [
            'package' => $package,
            'packageList' => $packageList,
            'showInternal' => $showInternal,
            'search' => $search,
            'total' => $listData['total'],
            'limit' => $limit,
            'limitStart' => $listData['limitstart'],
            'pageSizes' => $pageSizes,
            'rows' => $listData['rows'],
            'pagination' => $listData['pagination'],
        ];
    }

    protected function getTableName(): string
    {
        return '#__facileforms_pieces';
    }
}
