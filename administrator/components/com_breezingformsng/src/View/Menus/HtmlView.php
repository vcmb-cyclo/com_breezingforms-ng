<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\View\Menus;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Vcmb\Component\BreezingformsNG\Administrator\Model\MenuModel;

class HtmlView extends \Vcmb\Component\BreezingformsNG\Administrator\View\BreezingformsNG\HtmlView
{
    public array  $items    = [];
    public array  $packages = [];
    public array  $forms    = [];
    public array  $parents  = [];
    public string $pkg      = '';
    public ?\stdClass $item = null;

    public function display($tpl = null): void
    {
        $input   = Factory::getApplication()->getInput();
        $layout  = $input->getCmd('layout', 'default');
        $factory = Factory::getApplication()
            ->bootComponent('com_breezingformsng')
            ->getMVCFactory();

        /** @var MenuModel $model */
        $model = $factory->createModel('Menu', 'Administrator', ['ignore_request' => true]);

        if ($layout === 'edit') {

            $input->set('hidemainmenu', 1);

        }


        if ($layout === 'edit') {
            $id         = $input->getInt('id', 0);
            $formId     = $input->getInt('form_id', 0);
            $this->pkg  = $input->getString('pkg', '');

            $this->item    = $formId > 0 ? $model->prefillFromForm($formId) : $model->getItem($id);
            $this->pkg     = $this->item->package ?? $this->pkg;
            $this->forms   = $model->getForms();
            $this->parents = $model->getParents($this->pkg);

            ToolbarHelper::apply('menus.save');
            ToolbarHelper::cancel('menus.cancel');
        } else {
            $this->pkg      = $this->resolvedPkg($input, $model);
            $this->items    = $model->getItems($this->pkg);
            $this->packages = $model->getPackages();

            ToolbarHelper::addNew('menus.edit');
            ToolbarHelper::custom('menus.copy', 'copy', '', 'JLIB_HTML_BATCH_COPY', true);
            ToolbarHelper::publish('menus.publish', 'JPUBLISH', true);
            ToolbarHelper::unpublish('menus.unpublish', 'JUNPUBLISH', true);
            ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'menus.remove');
            ToolbarHelper::custom('menus.sync', 'refresh', '', 'COM_BREEZINGFORMSNG_MENUS_SYNC', false);

            $document = Factory::getApplication()->getDocument();
            $wa       = $document->getWebAssetManager();
            $wa->registerAndUseScript(
                'com_breezingformsng.admin-form',
                'media/com_breezingformsng/js/admin/admin-form.js',
                ['version' => 'auto'],
                ['defer' => true],
                ['core']
            );
            $document->addScriptOptions('com_breezingformsng.admin-form', ['confirmDeleteTask' => 'menus.remove']);
            Text::script('JGLOBAL_CONFIRM_DELETE');

            $wa->registerAndUseScript(
                'com_breezingformsng.admin-toggle-published',
                'media/com_breezingformsng/js/admin/admin-toggle-published.js',
                ['version' => 'auto'],
                ['defer' => true],
                ['core']
            );
            $document->addScriptOptions('com_breezingformsng.admin-toggle-published', ['csrfToken' => Session::getFormToken()]);
            Text::script('JPUBLISHED');
            Text::script('JUNPUBLISHED');
        }

        parent::display($tpl);
    }

    protected function getDetailLabel(): ?string
    {
        if ($this->item === null) {
            return null;
        }

        $title = trim((string) $this->item->title);

        return $title !== '' ? $title : Text::_('COM_BREEZINGFORMSNG_INSTALLER_UNKNOWN');
    }

    private function resolvedPkg(\Joomla\Input\Input $input, MenuModel $model): string
    {
        $session = Factory::getApplication()->getSession();
        $pkg     = $input->getString('pkg', '__unset__');

        if ($pkg === '__unset__') {
            $pkg = $session->get('bf.menus_pkg', '');
        } elseif ($pkg === '- blank -') {
            $pkg = '';
        }

        $packages = $model->getPackages();
        if ($pkg !== '' && !in_array($pkg, $packages, true)) {
            $pkg = $packages[0] ?? '';
        }

        $session->set('bf.menus_pkg', $pkg);
        return $pkg;
    }
}
