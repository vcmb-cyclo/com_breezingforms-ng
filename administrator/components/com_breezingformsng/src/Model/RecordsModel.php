<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Database\DatabaseInterface;

class RecordsModel extends BaseModel
{
    private const ALLOWED_SORTS = [
        'records.id'        => 'records.id',
        'records.submitted' => 'records.submitted',
        'forms.title'       => 'forms.title',
        'records.ip'        => 'records.ip',
        'records.username'  => 'records.username',
        'records.viewed'    => 'records.viewed',
        'records.exported'  => 'records.exported',
        'records.archived'  => 'records.archived',
    ];

    public function getForms(): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select id, title, name From #__facileforms_forms Order By `title`");
        return $db->loadAssocList();
    }

    public function getTotal(int $formSelection, string $searchTerm): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery(
            'Select Count(*) From #__facileforms_records As records'
            . ' Inner Join #__facileforms_forms As forms On forms.id = records.form'
            . $this->buildWhere($db, $formSelection, $searchTerm)
        );
        return (int) $db->loadResult();
    }

    public function getItems(int $formSelection, string $searchTerm, int $limitStart, int $limit, string $listOrder, string $listDirn): array
    {
        $orderCol = self::ALLOWED_SORTS[$listOrder] ?? 'records.submitted';
        $dir = strtoupper($listDirn) === 'ASC' ? 'ASC' : 'DESC';
        $orderSql = $orderCol . ' ' . $dir . ($orderCol !== 'records.id' ? ', records.id ' . $dir : '');

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery(
            'Select records.id, records.submitted, records.ip, records.user_id, records.username, records.user_full_name,'
            . ' records.viewed, records.exported, records.archived, records.paypal_tx_id,'
            . ' forms.title As form_title, forms.name As form_name, forms.id As form_id'
            . ' From #__facileforms_records As records'
            . ' Inner Join #__facileforms_forms As forms On forms.id = records.form'
            . $this->buildWhere($db, $formSelection, $searchTerm)
            . ' Order By ' . $orderSql,
            $limitStart,
            $limit
        );
        return $db->loadAssocList();
    }

    private function buildWhere($db, int $formSelection, string $searchTerm): string
    {
        $where = [];
        if ($formSelection > 0) {
            $where[] = 'records.form = ' . $formSelection;
        }
        if ($searchTerm !== '') {
            $q = $db->quote('%' . $searchTerm . '%');
            $exact = $db->quote($searchTerm);
            $where[] = '('
                . 'records.id = ' . $exact
                . ' Or records.ip Like ' . $q
                . ' Or records.username Like ' . $q
                . ' Or records.user_full_name Like ' . $q
                . ' Or forms.title Like ' . $q
                . ' Or forms.name Like ' . $q
                . ' Or records.paypal_tx_id Like ' . $q
                . ')';
        }
        return $where ? ' Where ' . implode(' And ', $where) : '';
    }
}
