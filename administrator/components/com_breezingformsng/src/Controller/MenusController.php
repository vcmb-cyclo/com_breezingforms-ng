<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Vcmb\Component\BreezingformsNG\Administrator\Model\MenuModel;

class MenusController extends BaseController
{
    public function display($cachable = false, $urlparams = []): static
    {
        $input = $this->app->getInput();
        $input->set('view', 'menus');
        return parent::display($cachable, $urlparams);
    }

    public function edit(): void
    {
        $input  = $this->app->getInput();
        $id     = $input->getInt('id', 0);
        $pkg    = $input->getString('pkg', '');
        $formId = $input->getInt('form_id', 0);

        $url = 'index.php?option=com_breezingformsng&view=menus&layout=edit'
            . '&id=' . $id . '&pkg=' . rawurlencode($pkg)
            . ($formId > 0 ? '&form_id=' . $formId : '');

        $this->app->redirect(Route::_($url, false));
    }

    public function save(): void
    {
        $app   = $this->app;
        $input = $app->getInput();

        if (!$this->checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            $app->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
            return;
        }

        $data = $input->post->getArray([
            'id'        => 'INT',
            'package'   => 'STRING',
            'parent'    => 'INT',
            'title'     => 'STRING',
            'name'      => 'STRING',
            'page'      => 'INT',
            'frame'     => 'INT',
            'border'    => 'INT',
            'img'       => 'STRING',
            'params'    => 'STRING',
            'published' => 'INT',
        ]);

        try {
            $id = $this->getMenuModel()->saveItem($data);
            $app->enqueueMessage(Text::_('JLIB_APPLICATION_SAVE_SUCCESS'), 'message');
            $app->redirect(Route::_($this->listUrl((string) ($data['package'] ?? '')), false));
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $pkg = (string) ($data['package'] ?? '');
            $id  = (int) ($data['id'] ?? 0);
            $app->redirect(Route::_(
                'index.php?option=com_breezingformsng&view=menus&layout=edit&id=' . $id . '&pkg=' . rawurlencode($pkg),
                false
            ));
        }
    }

    public function cancel(): void
    {
        $pkg = $this->app->getInput()->getString('pkg', '');
        $this->app->redirect(Route::_($this->listUrl($pkg), false));
    }

    public function remove(): void
    {
        $app   = $this->app;
        $input = $app->getInput();

        if (!$this->checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
        } else {
            $ids = (array) $input->get('cid', [], 'INT');
            if (!empty($ids)) {
                $this->getMenuModel()->deleteItems($ids);
                $app->enqueueMessage(Text::_('JLIB_APPLICATION_DELETE_SUCCESS'), 'message');
            }
        }

        $app->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
    }

    public function copy(): void
    {
        $app   = $this->app;
        $input = $app->getInput();

        if (!$this->checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
        } else {
            $ids = (array) $input->get('cid', [], 'INT');
            if (!empty($ids)) {
                $this->getMenuModel()->copyItems($ids);
                $app->enqueueMessage(Text::_('COM_BREEZINGFORMSNG_MENUS_SUCOPIED'), 'message');
            }
        }

        $app->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
    }

    public function setPublished(): void
    {
        $this->checkToken();

        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        $input = $this->app->getInput();
        $id    = $input->getInt('id', 0);
        $state = $input->getInt('state', 0);
        if ($id > 0) {
            $this->getMenuModel()->publish([$id], $state);
        }
        $this->app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        $this->app->setBody(json_encode(['Result' => 'OK'], JSON_THROW_ON_ERROR));
        $this->app->close();
    }

    public function publish(): void
    {
        $this->togglePublish(1);
    }

    public function unpublish(): void
    {
        $this->togglePublish(0);
    }

    public function orderup(): void
    {
        $this->moveOrder(-1);
    }

    public function orderdown(): void
    {
        $this->moveOrder(1);
    }

    public function sync(): void
    {
        $app   = $this->app;
        $input = $app->getInput();

        if (!$this->checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
        } else {
            try {
                $menusFactory = $app->bootComponent('com_menus')->getMVCFactory();
                $this->getMenuModel()->syncToJoomlaMenu($menusFactory);
                $app->enqueueMessage(Text::_('COM_BREEZINGFORMSNG_MENUS_SAVED'), 'message');
            } catch (\Throwable $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
            }
        }

        $app->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
    }

    private function togglePublish(int $state): void
    {
        $app   = $this->app;
        $input = $app->getInput();

        if (!$this->checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
        } else {
            $ids = (array) $input->get('cid', [], 'INT');
            if (!empty($ids)) {
                $this->getMenuModel()->publish($ids, $state);
            }
        }

        $app->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
    }

    private function moveOrder(int $inc): void
    {
        $app   = $this->app;
        $input = $app->getInput();

        if (!$this->checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
        } else {
            $ids = (array) $input->get('cid', [], 'INT');
            $pkg = $input->getString('pkg', '');
            if (!empty($ids)) {
                $this->getMenuModel()->moveOrder((int) $ids[0], $inc, $pkg);
            }
        }

        $app->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
    }

    private function listUrl(string $pkg): string
    {
        return 'index.php?option=com_breezingformsng&view=menus'
            . ($pkg !== '' ? '&pkg=' . rawurlencode($pkg) : '');
    }

    private function getMenuModel(): MenuModel
    {
        $model = $this->app
            ->bootComponent('com_breezingformsng')
            ->getMVCFactory()
            ->createModel('Menu', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof MenuModel) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }

        return $model;
    }
}
