<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

class MenuModel extends BaseDatabaseModel
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
                $db->quoteName('m.id'),
                $db->quoteName('m.package'),
                $db->quoteName('m.parent'),
                $db->quoteName('m.ordering'),
                $db->quoteName('m.published'),
                $db->quoteName('m.title'),
                $db->quoteName('m.name'),
                $db->quoteName('m.page'),
            ])
            ->from($db->quoteName('#__facileforms_compmenus', 'm'))
            ->order([$db->quoteName('m.parent') . ' ASC', $db->quoteName('m.ordering') . ' ASC']);

        if ($pkg !== '') {
            $q->where($db->quoteName('m.package') . ' = :package')
                ->bind(':package', $pkg);
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
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

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
            $q->where($db->quoteName('package') . ' = :package')
                ->bind(':package', $pkg);
        }

        return $db->setQuery($q)->loadObjectList() ?: [];
    }

    public function prefillFromForm(int $formId): \stdClass
    {
        $db  = $this->db();
        $q   = $db->getQuery(true)
            ->select([$db->quoteName('name'), $db->quoteName('title')])
            ->from($db->quoteName('#__facileforms_forms'))
            ->where($db->quoteName('id') . ' = :formId')
            ->bind(':formId', $formId, ParameterType::INTEGER);
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
            throw new \RuntimeException(Text::_('COM_BREEZINGFORMSNG_MENUS_TITLEEMPTY'));
        }

        if ($id > 0) {
            $q = $db->getQuery(true)
                ->update($db->quoteName('#__facileforms_compmenus'))
                ->set([
                    $db->quoteName('package') . ' = :package',
                    $db->quoteName('parent') . ' = :parent',
                    $db->quoteName('title') . ' = :title',
                    $db->quoteName('name') . ' = :name',
                    $db->quoteName('page') . ' = :page',
                    $db->quoteName('frame') . ' = :frame',
                    $db->quoteName('border') . ' = :border',
                    $db->quoteName('img') . ' = :image',
                    $db->quoteName('params') . ' = :params',
                    $db->quoteName('published') . ' = :published',
                ])
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $this->bindMenuValues($q, $pkg, $parent, $title, $name, $page, $frame, $border, $img, $params, $pub);
            $db->setQuery($q)->execute();
        } else {
            $maxQ = $db->getQuery(true)
                ->select('COALESCE(MAX(' . $db->quoteName('ordering') . '), 0) + 1')
                ->from($db->quoteName('#__facileforms_compmenus'))
                ->where($db->quoteName('parent') . ' = :parent')
                ->where($db->quoteName('package') . ' = :package')
                ->bind(':parent', $parent, ParameterType::INTEGER)
                ->bind(':package', $pkg);
            $ordering = (int) $db->setQuery($maxQ)->loadResult();

            $q = $db->getQuery(true)
                ->insert($db->quoteName('#__facileforms_compmenus'))
                ->columns([
                    $db->quoteName('package'), $db->quoteName('parent'), $db->quoteName('ordering'),
                    $db->quoteName('published'), $db->quoteName('img'), $db->quoteName('title'),
                    $db->quoteName('name'), $db->quoteName('page'), $db->quoteName('frame'),
                    $db->quoteName('border'), $db->quoteName('params'),
                ])
                ->values(':package, :parent, :ordering, :published, :image, :title, :name, :page, :frame, :border, :params')
                ->bind(':ordering', $ordering, ParameterType::INTEGER);
            $this->bindMenuValues($q, $pkg, $parent, $title, $name, $page, $frame, $border, $img, $params, $pub);
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
        $intIds = array_values(array_filter(array_map('intval', $ids)));

        if ($intIds === []) {
            return;
        }

        $childQ = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__facileforms_compmenus'))
            ->whereIn($db->quoteName('parent'), $intIds);
        $childIds = $db->setQuery($childQ)->loadColumn();

        $allIds = array_merge($intIds, array_map('intval', $childIds ?: []));
        $allIds = array_values(array_unique($allIds));

        $db->setQuery(
            $db->getQuery(true)
                ->delete($db->quoteName('#__facileforms_compmenus'))
                ->whereIn($db->quoteName('id'), $allIds)
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
                ->where($db->quoteName('parent') . ' = :parent')
                ->bind(':parent', $id, ParameterType::INTEGER);
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
        $intIds = array_values(array_filter(array_map('intval', $ids)));

        if ($intIds === []) {
            return;
        }

        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__facileforms_compmenus'))
                ->set($db->quoteName('published') . ' = :state')
                ->whereIn($db->quoteName('id'), $intIds)
                ->bind(':state', $state, ParameterType::INTEGER)
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
        $parent = (int) $item->parent;
        $ordering = (int) $item->ordering;
        $neighbor = $db->setQuery(
            $db->getQuery(true)
                ->select(['id', 'ordering'])
                ->from($db->quoteName('#__facileforms_compmenus'))
                ->where($db->quoteName('parent') . ' = :parent')
                ->where($db->quoteName('package') . ' = :package')
                ->where($db->quoteName('ordering') . ' ' . $dir . ' :ordering')
                ->order($db->quoteName('ordering') . ' ' . $sort)
                ->bind(':parent', $parent, ParameterType::INTEGER)
                ->bind(':package', $pkg)
                ->bind(':ordering', $ordering, ParameterType::INTEGER)
                ->setLimit(1)
        )->loadObject();

        if ($neighbor === null) {
            return;
        }

        $neighborId = (int) $neighbor->id;
        $neighborOrdering = (int) $neighbor->ordering;

        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__facileforms_compmenus'))
                ->set($db->quoteName('ordering') . ' = :ordering')
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':ordering', $neighborOrdering, ParameterType::INTEGER)
                ->bind(':id', $id, ParameterType::INTEGER)
        )->execute();

        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__facileforms_compmenus'))
                ->set($db->quoteName('ordering') . ' = :ordering')
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':ordering', $ordering, ParameterType::INTEGER)
                ->bind(':id', $neighborId, ParameterType::INTEGER)
        )->execute();
    }

    public function syncToJoomlaMenu(MVCFactoryInterface $menusFactory): void
    {
        $db = $this->db();
        $linkPattern = 'index.php?option=com_breezingformsng&act=run%';
        $existingQuery = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' LIKE :linkPattern')
            ->where($db->quoteName('checked_out') . ' = 0')
            ->bind(':linkPattern', $linkPattern);
        $existingIds = array_map('intval', $db->setQuery($existingQuery)->loadColumn() ?: []);

        $menuQ = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__facileforms_compmenus'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('id') . ' ASC');
        $items = $db->setQuery($menuQ)->loadObjectList() ?: [];

        $componentLink = 'index.php?option=com_breezingformsng';
        $parentRowQ = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' = :componentLink')
            ->where($db->quoteName('client_id') . ' = 1')
            ->bind(':componentLink', $componentLink);
        $rootMenuId = (int) $db->setQuery($parentRowQ)->loadResult();

        if ($rootMenuId < 1) {
            return;
        }

        $extensionType = 'component';
        $element = 'com_breezingformsng';
        $extensionQuery = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = :extensionType')
            ->where($db->quoteName('element') . ' = :element')
            ->bind(':extensionType', $extensionType)
            ->bind(':element', $element);
        $componentId = (int) $db->setQuery($extensionQuery)->loadResult();

        if ($componentId < 1) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }

        $idMap = [];

        $db->transactionStart();

        try {
            foreach ($existingIds as $existingId) {
                $table = $menusFactory->createTable('Menu', 'Administrator');

                if (!$table || !$table->delete($existingId, true)) {
                    throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
                }
            }

            foreach ($items as $item) {
                $parentId = (int) $item->parent;
                $joomlaParentId = $parentId > 0 && isset($idMap[$parentId])
                    ? $idMap[$parentId]
                    : $rootMenuId;
                $title = (string) $item->title;
                $alias = OutputFilter::stringURLSafe($title) . '-' . (int) $item->id;
                $table = $menusFactory->createTable('Menu', 'Administrator');

                if (!$table) {
                    throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
                }

                $table->setLocation($joomlaParentId, 'last-child');
                $data = [
                    'menutype' => 'main',
                    'title' => $title,
                    'alias' => $alias,
                    'link' => 'index.php?option=com_breezingformsng&act=run&ff_name=' . rawurlencode((string) $item->name),
                    'type' => 'component',
                    'published' => 1,
                    'parent_id' => $joomlaParentId,
                    'component_id' => $componentId,
                    'browserNav' => 0,
                    'access' => 0,
                    'params' => '',
                    'home' => 0,
                    'language' => '*',
                    'client_id' => 1,
                ];

                if (!$table->bind($data) || !$table->check() || !$table->store()) {
                    throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
                }

                $idMap[(int) $item->id] = (int) $table->id;
            }

            $db->transactionCommit();
        } catch (\Throwable $exception) {
            $db->transactionRollback();
            throw $exception;
        }
    }

    private function bindMenuValues(
        QueryInterface $query,
        string $package,
        int $parent,
        string $title,
        string $name,
        int $page,
        int $frame,
        int $border,
        string $image,
        string $params,
        int $published
    ): void {
        $query
            ->bind(':package', $package)
            ->bind(':parent', $parent, ParameterType::INTEGER)
            ->bind(':title', $title)
            ->bind(':name', $name)
            ->bind(':page', $page, ParameterType::INTEGER)
            ->bind(':frame', $frame, ParameterType::INTEGER)
            ->bind(':border', $border, ParameterType::INTEGER)
            ->bind(':image', $image)
            ->bind(':params', $params)
            ->bind(':published', $published, ParameterType::INTEGER);
    }

    private function reorder(int $parent, string $pkg): void
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__facileforms_compmenus'))
            ->where($db->quoteName('parent') . ' = :parent')
            ->where($db->quoteName('package') . ' = :package')
            ->bind(':parent', $parent, ParameterType::INTEGER)
            ->bind(':package', $pkg)
            ->order($db->quoteName('ordering') . ' ASC');

        $ids = $db->setQuery($q)->loadColumn() ?: [];

        foreach (array_values($ids) as $pos => $rowId) {
            $ordering = $pos + 1;
            $rowId = (int) $rowId;
            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->quoteName('#__facileforms_compmenus'))
                    ->set($db->quoteName('ordering') . ' = :ordering')
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':ordering', $ordering, ParameterType::INTEGER)
                    ->bind(':id', $rowId, ParameterType::INTEGER)
            )->execute();
        }
    }
}
