<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Database\DatabaseInterface;

class MenuModel extends BaseModel
{
    private function db(): DatabaseInterface
    {
        return $this->getDatabase();
    }

    public function getPackages(): array
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('package'))
            ->from($db->quoteName('#__facileforms_compmenus'))
            ->order($db->quoteName('package') . ' ASC');

        return $db->setQuery($q)->loadColumn() ?: [];
    }

    public function getItems(string $pkg): array
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select([
                $db->quoteName('m', 'id'),
                $db->quoteName('m', 'package'),
                $db->quoteName('m', 'parent'),
                $db->quoteName('m', 'ordering'),
                $db->quoteName('m', 'published'),
                $db->quoteName('m', 'title'),
                $db->quoteName('m', 'name'),
                $db->quoteName('m', 'page'),
            ])
            ->from($db->quoteName('#__facileforms_compmenus', 'm'))
            ->order([$db->quoteName('m.parent') . ' ASC', $db->quoteName('m.ordering') . ' ASC']);

        if ($pkg !== '') {
            $q->where($db->quoteName('m.package') . ' = ' . $db->quote($pkg));
        }

        return $db->setQuery($q)->loadObjectList() ?: [];
    }

    public function getItem(int $id): ?\stdClass
    {
        if ($id <= 0) {
            $obj            = new \stdClass();
            $obj->id        = 0;
            $obj->package   = '';
            $obj->parent    = 0;
            $obj->ordering  = 0;
            $obj->published = 1;
            $obj->img       = '';
            $obj->title     = '';
            $obj->name      = '';
            $obj->page      = 1;
            $obj->frame     = 0;
            $obj->border    = 0;
            $obj->params    = '';
            return $obj;
        }

        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__facileforms_compmenus'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($id));

        return $db->setQuery($q)->loadObject() ?: null;
    }

    public function getForms(): array
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('name'), $db->quoteName('title')])
            ->from($db->quoteName('#__facileforms_forms'))
            ->order($db->quoteName('title') . ' ASC');

        return $db->setQuery($q)->loadObjectList() ?: [];
    }

    public function getParents(string $pkg): array
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('title')])
            ->from($db->quoteName('#__facileforms_compmenus'))
            ->where($db->quoteName('parent') . ' = 0')
            ->order($db->quoteName('ordering') . ' ASC');

        if ($pkg !== '') {
            $q->where($db->quoteName('package') . ' = ' . $db->quote($pkg));
        }

        return $db->setQuery($q)->loadObjectList() ?: [];
    }

    public function prefillFromForm(int $formId): \stdClass
    {
        $db  = $this->db();
        $q   = $db->getQuery(true)
            ->select([$db->quoteName('name'), $db->quoteName('title')])
            ->from($db->quoteName('#__facileforms_forms'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($formId));
        $form = $db->setQuery($q)->loadObject();

        $obj            = new \stdClass();
        $obj->id        = 0;
        $obj->package   = '';
        $obj->parent    = 0;
        $obj->ordering  = 0;
        $obj->published = 1;
        $obj->img       = '';
        $obj->title     = $form ? (string) $form->title : '';
        $obj->name      = $form ? (string) $form->name  : '';
        $obj->page      = 1;
        $obj->frame     = 0;
        $obj->border    = 0;
        $obj->params    = '';
        return $obj;
    }

    public function saveItem(array $data): int
    {
        $db = $this->db();

        $id      = (int) ($data['id'] ?? 0);
        $pkg     = (string) ($data['package'] ?? '');
        $parent  = (int) ($data['parent']  ?? 0);
        $title   = (string) ($data['title']   ?? '');
        $name    = (string) ($data['name']    ?? '');
        $page    = (int) ($data['page']    ?? 1);
        $frame   = (int) ($data['frame']   ?? 0);
        $border  = (int) ($data['border']  ?? 0);
        $img     = (string) ($data['img']     ?? '');
        $params  = (string) ($data['params']  ?? '');
        $pub     = (int) ($data['published'] ?? 1);

        if ($title === '') {
            throw new \RuntimeException(\Joomla\CMS\Language\Text::_('COM_BREEZINGFORMSNG_MENUS_TITLEEMPTY'));
        }

        if ($id > 0) {
            $q = $db->getQuery(true)
                ->update($db->quoteName('#__facileforms_compmenus'))
                ->set([
                    $db->quoteName('package')   . ' = ' . $db->quote($pkg),
                    $db->quoteName('parent')    . ' = ' . $db->quote($parent),
                    $db->quoteName('title')     . ' = ' . $db->quote($title),
                    $db->quoteName('name')      . ' = ' . $db->quote($name),
                    $db->quoteName('page')      . ' = ' . $db->quote($page),
                    $db->quoteName('frame')     . ' = ' . $db->quote($frame),
                    $db->quoteName('border')    . ' = ' . $db->quote($border),
                    $db->quoteName('img')       . ' = ' . $db->quote($img),
                    $db->quoteName('params')    . ' = ' . $db->quote($params),
                    $db->quoteName('published') . ' = ' . $db->quote($pub),
                ])
                ->where($db->quoteName('id') . ' = ' . $db->quote($id));
            $db->setQuery($q)->execute();
        } else {
            $maxQ = $db->getQuery(true)
                ->select('COALESCE(MAX(' . $db->quoteName('ordering') . '), 0) + 1')
                ->from($db->quoteName('#__facileforms_compmenus'))
                ->where($db->quoteName('parent') . ' = ' . $db->quote($parent))
                ->where($db->quoteName('package') . ' = ' . $db->quote($pkg));
            $ordering = (int) $db->setQuery($maxQ)->loadResult();

            $q = $db->getQuery(true)
                ->insert($db->quoteName('#__facileforms_compmenus'))
                ->columns([
                    $db->quoteName('package'), $db->quoteName('parent'), $db->quoteName('ordering'),
                    $db->quoteName('published'), $db->quoteName('img'), $db->quoteName('title'),
                    $db->quoteName('name'), $db->quoteName('page'), $db->quoteName('frame'),
                    $db->quoteName('border'), $db->quoteName('params'),
                ])
                ->values(implode(',', [
                    $db->quote($pkg), $db->quote($parent), $db->quote($ordering),
                    $db->quote($pub), $db->quote($img), $db->quote($title),
                    $db->quote($name), $db->quote($page), $db->quote($frame),
                    $db->quote($border), $db->quote($params),
                ]));
            $db->setQuery($q)->execute();
            $id = (int) $db->insertid();
        }

        $this->reorder($parent, $pkg);
        return $id;
    }

    public function deleteItems(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $db      = $this->db();
        $intIds  = array_map('intval', $ids);
        $inList  = implode(',', $intIds);

        $childQ = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__facileforms_compmenus'))
            ->where($db->quoteName('parent') . ' IN (' . $inList . ')');
        $childIds = $db->setQuery($childQ)->loadColumn();

        $allIds = array_merge($intIds, array_map('intval', $childIds ?: []));
        $allIn  = implode(',', array_unique($allIds));

        $db->setQuery(
            $db->getQuery(true)
                ->delete($db->quoteName('#__facileforms_compmenus'))
                ->where($db->quoteName('id') . ' IN (' . $allIn . ')')
        )->execute();
    }

    public function copyItems(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $db = $this->db();
        foreach (array_map('intval', $ids) as $id) {
            $src = $this->getItem($id);
            if ($src === null) {
                continue;
            }

            $data           = (array) $src;
            $data['id']     = 0;
            $data['title']  = $src->title . ' (copy)';
            $newId          = $this->saveItem($data);

            $childQ = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__facileforms_compmenus'))
                ->where($db->quoteName('parent') . ' = ' . $db->quote($id));
            $children = $db->setQuery($childQ)->loadObjectList() ?: [];

            foreach ($children as $child) {
                $cd          = (array) $child;
                $cd['id']    = 0;
                $cd['parent']= $newId;
                $this->saveItem($cd);
            }
        }
    }

    public function publish(array $ids, int $state): void
    {
        if (empty($ids)) {
            return;
        }

        $db     = $this->db();
        $intIds = implode(',', array_map('intval', $ids));
        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__facileforms_compmenus'))
                ->set($db->quoteName('published') . ' = ' . $db->quote($state))
                ->where($db->quoteName('id') . ' IN (' . $intIds . ')')
        )->execute();
    }

    public function moveOrder(int $id, int $inc, string $pkg): void
    {
        $item = $this->getItem($id);
        if ($item === null) {
            return;
        }

        $db = $this->db();

        $dir      = $inc > 0 ? '>' : '<';
        $sort     = $inc > 0 ? 'ASC' : 'DESC';
        $neighbor = $db->setQuery(
            $db->getQuery(true)
                ->select(['id', 'ordering'])
                ->from($db->quoteName('#__facileforms_compmenus'))
                ->where($db->quoteName('parent')  . ' = ' . $db->quote($item->parent))
                ->where($db->quoteName('package') . ' = ' . $db->quote($pkg))
                ->where($db->quoteName('ordering') . ' ' . $dir . ' ' . $db->quote($item->ordering))
                ->order($db->quoteName('ordering') . ' ' . $sort)
        )->loadObject();

        if ($neighbor === null) {
            return;
        }

        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__facileforms_compmenus'))
                ->set($db->quoteName('ordering') . ' = ' . $db->quote($neighbor->ordering))
                ->where($db->quoteName('id') . ' = ' . $db->quote($id))
        )->execute();

        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__facileforms_compmenus'))
                ->set($db->quoteName('ordering') . ' = ' . $db->quote($item->ordering))
                ->where($db->quoteName('id') . ' = ' . $db->quote($neighbor->id))
        )->execute();
    }

    public function syncToJoomlaMenu(): void
    {
        $db = $this->db();

        $protectedQ = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_breezingformsng&act=run%'))
            ->where($db->quoteName('checked_out') . ' != 0');
        $protected = $db->setQuery($protectedQ)->loadColumn() ?: [];

        $deleteQ = $db->getQuery(true)
            ->delete($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_breezingformsng&act=run%'));

        if (!empty($protected)) {
            $deleteQ->where($db->quoteName('id') . ' NOT IN (' . implode(',', array_map('intval', $protected)) . ')');
        }

        $db->setQuery($deleteQ)->execute();

        $menuQ = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__facileforms_compmenus'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('id') . ' ASC');
        $items = $db->setQuery($menuQ)->loadObjectList() ?: [];

        $parentRowQ = $db->getQuery(true)
            ->select(['id', 'lft', 'rgt', 'level', 'client_id'])
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_breezingformsng'))
            ->where($db->quoteName('client_id') . ' = 1');
        $parentRow = $db->setQuery($parentRowQ)->loadObject();

        if ($parentRow === null) {
            return;
        }

        $idMap = [];

        foreach ($items as $item) {
            $link  = 'index.php?option=com_breezingformsng&act=run&ff_name=' . rawurlencode((string) $item->name);
            $title = (string) $item->title;

            $parentId    = (int) $item->parent;
            $joomlaParentId = $parentId > 0 && isset($idMap[$parentId])
                ? $idMap[$parentId]
                : (int) $parentRow->id;

            $db->setQuery(
                $db->getQuery(true)
                    ->select(['rgt', 'level'])
                    ->from($db->quoteName('#__menu'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($joomlaParentId))
            );
            $parentData = $db->loadObject();
            if ($parentData === null) {
                continue;
            }

            $rgt   = (int) $parentData->rgt;
            $level = (int) $parentData->level + 1;

            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->quoteName('#__menu'))
                    ->set($db->quoteName('lft') . ' = ' . $db->quoteName('lft') . ' + 2')
                    ->where($db->quoteName('lft') . ' >= ' . $db->quote($rgt))
                    ->where($db->quoteName('client_id') . ' = 1')
            )->execute();

            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->quoteName('#__menu'))
                    ->set($db->quoteName('rgt') . ' = ' . $db->quoteName('rgt') . ' + 2')
                    ->where($db->quoteName('rgt') . ' >= ' . $db->quote($rgt))
                    ->where($db->quoteName('client_id') . ' = 1')
            )->execute();

            $q = $db->getQuery(true)
                ->insert($db->quoteName('#__menu'))
                ->columns([
                    $db->quoteName('menutype'), $db->quoteName('title'), $db->quoteName('alias'),
                    $db->quoteName('note'), $db->quoteName('link'), $db->quoteName('type'),
                    $db->quoteName('published'), $db->quoteName('parent_id'), $db->quoteName('level'),
                    $db->quoteName('component_id'), $db->quoteName('checked_out'),
                    $db->quoteName('checked_out_time'), $db->quoteName('browserNav'),
                    $db->quoteName('access'), $db->quoteName('img'), $db->quoteName('template_style_id'),
                    $db->quoteName('params'), $db->quoteName('lft'), $db->quoteName('rgt'),
                    $db->quoteName('home'), $db->quoteName('language'), $db->quoteName('client_id'),
                ])
                ->values(implode(',', [
                    $db->quote(''), $db->quote($title), $db->quote($title),
                    $db->quote(''), $db->quote($link), $db->quote('component'),
                    $db->quote(1), $db->quote($joomlaParentId), $db->quote($level),
                    $db->quote(0), $db->quote(0),
                    $db->quote('0000-00-00 00:00:00'), $db->quote(0),
                    $db->quote(0), $db->quote(''), $db->quote(0),
                    $db->quote(''), $db->quote($rgt), $db->quote($rgt + 1),
                    $db->quote(0), $db->quote('*'), $db->quote(1),
                ]));

            $db->setQuery($q)->execute();
            $idMap[(int) $item->id] = (int) $db->insertid();
        }
    }

    private function reorder(int $parent, string $pkg): void
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__facileforms_compmenus'))
            ->where($db->quoteName('parent')  . ' = ' . $db->quote($parent))
            ->where($db->quoteName('package') . ' = ' . $db->quote($pkg))
            ->order($db->quoteName('ordering') . ' ASC');

        $ids = $db->setQuery($q)->loadColumn() ?: [];

        foreach (array_values($ids) as $pos => $rowId) {
            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->quoteName('#__facileforms_compmenus'))
                    ->set($db->quoteName('ordering') . ' = ' . $db->quote($pos + 1))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($rowId))
            )->execute();
        }
    }
}
