<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Vcmb\Component\BreezingformsNG\Administrator\Model\PieceModel;
use Vcmb\Component\BreezingformsNG\Administrator\Model\ScriptModel;

class DisplayController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_breezingformsng')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $input = Factory::getApplication()->getInput();
        $act = $input->getCmd('act', '');
        $task = $input->getCmd('task', '');
        $view = $input->getCmd('view', '');

        if ($task === '' && in_array($act, ['managepieces', 'managescripts'], true)) {
            $view = $act === 'managepieces' ? 'pieces' : 'scripts';
            $input->set('view', $view);
        }

        if ($task === '' && $view === 'help') {
            return parent::display($cachable, $urlparams);
        }

        if ($task === '' && (
            $view === 'records'
            || in_array($act, ['managerecs', 'recordmanagement'], true)
            || ($act === '' && $view === '')
        )) {
            $input->set('view', 'records');
            $input->set('act', '');
            return parent::display($cachable, $urlparams);
        }

        if ($act === 'configuration') {
            Factory::getApplication()->redirect(
                'index.php?option=com_config&view=component&component=com_breezingformsng'
            );
        }

        if ($task === '' && ($view === 'integrator' || $act === 'integrate')) {
            $input->set('view', 'integrator');
            $input->set('act', '');
            return parent::display($cachable, $urlparams);
        }

        if ($act === 'manageforms' || ($task === '' && $view === 'forms')) {
            $input->set('view', 'forms');
            $input->set('act', '');
            if ($task === '' || $task === 'listitems') {
                $input->set('task', '');
                return parent::display($cachable, $urlparams);
            }
            $taskMap = [
                'new'       => 'forms.edit',
                'edit'      => 'forms.edit',
                'save'      => 'forms.save',
                'cancel'    => 'forms.cancel',
                'copy'      => 'forms.copy',
                'remove'    => 'forms.remove',
                'publish'   => 'forms.publish',
                'unpublish' => 'forms.unpublish',
                'orderup'   => 'forms.orderup',
                'orderdown' => 'forms.orderdown',
            ];
            if (isset($taskMap[$task])) {
                $input->set('task', $taskMap[$task]);
            }
            return parent::display($cachable, $urlparams);
        }

        if ($task === '' && ($view === 'menus' || $act === 'managemenus')) {
            $input->set('view', 'menus');
            $input->set('act', '');
            return parent::display($cachable, $urlparams);
        }

        if ($task === '' && in_array($view, ['pieces', 'scripts'], true)) {
            $this->bootstrapLegacyGlobals();
            $this->prepareListPackage($view);

            return parent::display($cachable, $urlparams);
        }

        // No valid route matched — return without invoking the removed legacy bridge.
        return $this;
    }

    private function bootstrapLegacyGlobals(): void
    {
        global $ff_mospath, $ff_admpath, $ff_compath;
        global $ff_mossite, $ff_admsite, $ff_admicon, $ff_comsite;
        global $ff_config, $ff_compatible, $ff_install;
        global $database, $task;

        if (isset($ff_config)) {
            return;
        }

        $database = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);

        $task       = '';
        $comppath   = '/components/com_breezingformsng';
        $ff_admpath = str_replace('\\', '/', JPATH_ADMINISTRATOR . '/components/com_breezingformsng');
        $ff_mospath = str_replace('\\', '/', dirname(dirname(dirname($ff_admpath))));
        $ff_compath = $ff_mospath . $comppath;
        $ff_admsite = $ff_mospath . '/administrator' . $comppath;
        $ff_admicon = $ff_admsite . '/images/icons';

        require_once JPATH_SITE . '/components/com_breezingformsng/facileforms.class.php';

        $ff_config = (object) [
            'areasmall'  => 4,
            'areamedium' => 12,
            'arealarge'  => 20,
            'limitdesc'  => 100,
            'piecepkg'   => '',
            'scriptpkg'  => '',
        ];
        $ff_compatible = true;
        $ff_install    = false;
    }

    private function prepareListPackage(string $view): void
    {
        $input      = Factory::getApplication()->getInput();
        $session    = Factory::getApplication()->getSession();
        $sessionKey = $view === 'pieces' ? 'bf.piecepkg' : 'bf.scriptpkg';

        $package = $input->get('pkg', null, 'STRING');

        if ($package === null) {
            $package = (string) ($session->get($sessionKey) ?? '');
        } elseif ($package === '- blank -') {
            $package = '';
        }

        if ($package !== '') {
            $model = Factory::getApplication()
                ->bootComponent('com_breezingformsng')
                ->getMVCFactory()
                ->createModel($view === 'pieces' ? 'Piece' : 'Script', 'Administrator', ['ignore_request' => true]);

            if (
                ($view === 'pieces' && !$model instanceof PieceModel)
                || ($view === 'scripts' && !$model instanceof ScriptModel)
            ) {
                throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
            }

            if (!$model->packageExists((string) $package)) {
                $package = '';
            }
        }

        $session->set($sessionKey, $package);
        $input->set('pkg', $package);
    }

}
