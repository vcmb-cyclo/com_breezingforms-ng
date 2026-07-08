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

class FormsModel extends BaseModel
{
    private const ALLOWED_SORTS = [
        'id'          => 'id',
        'title'       => 'title',
        'name'        => 'name',
        'pages'       => 'pages',
        'description' => 'description',
        'modified'    => 'modified',
        'published'   => 'published',
        'ordering'    => 'ordering',
    ];

    private function db(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    public function getPackages(): array
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('package'))
            ->from($db->quoteName('#__facileforms_forms'))
            ->where($db->quoteName('package') . ' IS NOT NULL')
            ->where($db->quoteName('package') . ' != ' . $db->quote(''))
            ->order($db->quoteName('package') . ' ASC');

        return $db->setQuery($q)->loadColumn() ?: [];
    }

    public function getItems(
        string $pkg,
        string $search,
        string $filterState,
        string $sort,
        string $dir,
        int $limit,
        int $limitStart
    ): array {
        $db      = $this->db();
        $sortCol = self::ALLOWED_SORTS[$sort] ?? 'ordering';
        $dirSql  = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        $q = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__facileforms_forms'));

        $this->applyFilters($q, $db, $pkg, $search, $filterState);
        $q->order($db->quoteName($sortCol) . ' ' . $dirSql . ', ' . $db->quoteName('id') . ' DESC');

        return $db->setQuery($q, $limitStart, $limit)->loadObjectList() ?: [];
    }

    public function getTotal(string $pkg, string $search, string $filterState): int
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__facileforms_forms'));

        $this->applyFilters($q, $db, $pkg, $search, $filterState);

        return (int) $db->setQuery($q)->loadResult();
    }

    public function getScripts(string $type): array
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select([$db->quoteName('id'), "CONCAT(" . $db->quoteName('package') . ", '::', " . $db->quoteName('name') . ") AS text"])
            ->from($db->quoteName('#__facileforms_scripts'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('type') . ' = ' . $db->quote($type))
            ->order('text, ' . $db->quoteName('id') . ' DESC');

        return $db->setQuery($q)->loadObjectList() ?: [];
    }

    public function getPieces(string $type): array
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select([$db->quoteName('id'), "CONCAT(" . $db->quoteName('package') . ", '::', " . $db->quoteName('name') . ") AS text"])
            ->from($db->quoteName('#__facileforms_pieces'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('type') . ' = ' . $db->quote($type))
            ->order('text, ' . $db->quoteName('id') . ' DESC');

        return $db->setQuery($q)->loadObjectList() ?: [];
    }

    private function applyFilters(\Joomla\Database\QueryInterface $q, DatabaseInterface $db, string $pkg, string $search, string $filterState): void
    {
        if ($pkg !== '') {
            $q->where($db->quoteName('package') . ' = ' . $db->quote($pkg));
        }
        if ($search !== '') {
            $like = $db->quote('%' . $db->escape($search, true) . '%');
            $q->where('(' .
                $db->quoteName('title') . ' LIKE ' . $like . ' OR ' .
                $db->quoteName('name')  . ' LIKE ' . $like . ' OR ' .
                $db->quoteName('description') . ' LIKE ' . $like .
            ')');
        }
        if ($filterState === 'P') {
            $q->where($db->quoteName('published') . ' = 1');
        } elseif ($filterState === 'U') {
            $q->where($db->quoteName('published') . ' = 0');
        }
    }

    public function resolvedPkg(string $pkg): string
    {
        $session  = Factory::getApplication()->getSession();
        $packages = $this->getPackages();

        if ($pkg === '__unset__') {
            $pkg = (string) $session->get('bf.forms_pkg', '');
        } elseif ($pkg === '- blank -') {
            $pkg = '';
        }

        if ($pkg !== '' && !in_array($pkg, $packages, true)) {
            $pkg = $packages[0] ?? '';
        }

        $session->set('bf.forms_pkg', $pkg);
        return $pkg;
    }
}
