<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

class RecordsModel extends BaseDatabaseModel
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
        'records.modified'  => 'records.modified',
    ];

    public function getForms(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['id', 'title', 'name'])
            ->from($db->quoteName('#__facileforms_forms'))
            ->order($db->quoteName('title'));
        $db->setQuery($query);
        return $db->loadAssocList();
    }

    public function getTotal(int $formSelection, string $searchTerm): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__facileforms_records', 'records'))
            ->join('INNER', $db->quoteName('#__facileforms_forms', 'forms') . ' ON forms.id = records.form');
        $this->applyWhere($query, $db, $formSelection, $searchTerm);
        $db->setQuery($query);
        return (int) $db->loadResult();
    }

    public function getItems(int $formSelection, string $searchTerm, int $limitStart, int $limit, string $listOrder, string $listDirn): array
    {
        $orderCol = self::ALLOWED_SORTS[$listOrder] ?? 'records.submitted';
        $dir = strtoupper($listDirn) === 'ASC' ? 'ASC' : 'DESC';
        $orderSql = $orderCol . ' ' . $dir . ($orderCol !== 'records.id' ? ', records.id ' . $dir : '');

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'records.id', 'records.submitted', 'records.modified', 'records.ip',
                'records.user_id', 'records.username', 'records.user_full_name',
                'records.viewed', 'records.exported', 'records.archived', 'records.paypal_tx_id',
                'forms.title AS form_title', 'forms.name AS form_name', 'forms.id AS form_id',
            ])
            ->from($db->quoteName('#__facileforms_records', 'records'))
            ->join('INNER', $db->quoteName('#__facileforms_forms', 'forms') . ' ON forms.id = records.form')
            ->order($orderSql);
        $this->applyWhere($query, $db, $formSelection, $searchTerm);
        $db->setQuery($query, $limitStart, $limit);
        return $db->loadAssocList();
    }

    private function applyWhere(QueryInterface $query, DatabaseInterface $db, int $formSelection, string $searchTerm): void
    {
        if ($formSelection > 0) {
            $query->where('records.form = :formSelection')
                ->bind(':formSelection', $formSelection, ParameterType::INTEGER);
        }

        if ($searchTerm !== '') {
            $like = '%' . $searchTerm . '%';
            $conditions = [
                'records.id = :searchExact',
                $db->quoteName('records.ip') . ' LIKE :searchLike1',
                $db->quoteName('records.username') . ' LIKE :searchLike2',
                $db->quoteName('records.user_full_name') . ' LIKE :searchLike3',
                $db->quoteName('forms.title') . ' LIKE :searchLike4',
                $db->quoteName('forms.name') . ' LIKE :searchLike5',
                $db->quoteName('records.paypal_tx_id') . ' LIKE :searchLike6',
            ];

            if ($formSelection > 0) {
                $query->extendWhere('AND', $conditions, 'OR');
            } else {
                $query->where($conditions, 'OR');
            }

            $query->bind(':searchExact', $searchTerm, ParameterType::STRING)
                ->bind(':searchLike1', $like, ParameterType::STRING)
                ->bind(':searchLike2', $like, ParameterType::STRING)
                ->bind(':searchLike3', $like, ParameterType::STRING)
                ->bind(':searchLike4', $like, ParameterType::STRING)
                ->bind(':searchLike5', $like, ParameterType::STRING)
                ->bind(':searchLike6', $like, ParameterType::STRING);
        }
    }
}
