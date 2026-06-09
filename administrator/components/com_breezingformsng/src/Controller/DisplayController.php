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
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
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

        if ($task === '' && in_array($view, ['pieces', 'scripts'], true)) {
            $this->bootstrapLegacyListRenderer();
            $this->prepareListPackage($view);

            return parent::display($cachable, $urlparams);
        }

        $this->setLegacyPageHeader($act);

        require JPATH_ADMINISTRATOR . '/components/com_breezingformsng/admin.breezingforms.php';

        return $this;
    }

    private function bootstrapLegacyListRenderer(): void
    {
        global $ff_mospath, $ff_admpath, $ff_compath;
        global $ff_mossite, $ff_admsite, $ff_admicon, $ff_comsite;
        global $ff_config, $ff_compatible, $ff_install;
        global $task;

        if (isset($ff_config)) {
            return;
        }

        $task = '';
        $comppath = '/components/com_breezingformsng';
        $ff_admpath = str_replace('\\', '/', JPATH_ADMINISTRATOR . '/components/com_breezingformsng');
        $ff_mospath = str_replace('\\', '/', dirname(dirname(dirname($ff_admpath))));
        $ff_compath = $ff_mospath . $comppath;

        require JPATH_ADMINISTRATOR . '/components/com_breezingformsng/toolbar.facileforms.php';
    }

    private function prepareListPackage(string $view): void
    {
        global $ff_config;

        $input = Factory::getApplication()->getInput();
        $package = $input->get('pkg', null, 'STRING');

        if ($package === null) {
            $package = $view === 'pieces' ? (string) $ff_config->piecepkg : (string) $ff_config->scriptpkg;
        } elseif ($package === '- blank -') {
            $package = '';
        }

        if ($package !== '') {
            $model = Factory::getApplication()
                ->bootComponent('com_breezingformsng')
                ->getMVCFactory()
                ->createModel($view === 'pieces' ? 'Piece' : 'Script', 'Administrator', ['ignore_request' => true]);
            $fallback = $view === 'pieces' ? (string) $ff_config->piecepkg : (string) $ff_config->scriptpkg;

            if (
                ($view === 'pieces' && !$model instanceof PieceModel)
                || ($view === 'scripts' && !$model instanceof ScriptModel)
            ) {
                throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
            }

            if (!$model->packageExists((string) $package)) {
                $package = $fallback;
            }
        }

        if ($view === 'pieces' && $package !== (string) $ff_config->piecepkg) {
            $ff_config->piecepkg = $package;
            $ff_config->store();
        }

        if ($view === 'scripts' && $package !== (string) $ff_config->scriptpkg) {
            $ff_config->scriptpkg = $package;
            $ff_config->store();
        }

        $input->set('pkg', $package);
    }

    private function setLegacyPageHeader(string $act): void
    {
        $input = Factory::getApplication()->getInput();
        $section = match ($act) {
            'managerecs', 'recordmanagement', '' => Text::_('COM_BREEZINGFORMSNG_MANAGERECS'),
            'manageforms', 'easymode', 'quickmode' => Text::_('COM_BREEZINGFORMSNG_MANAGEFORMS'),
            'managescripts' => Text::_('COM_BREEZINGFORMSNG_MANAGESCRIPTS'),
            'managepieces' => Text::_('COM_BREEZINGFORMSNG_MANAGEPIECES'),
            'integrate' => Text::_('COM_BREEZINGFORMSNG_INTEGRATOR'),
            'configuration' => Text::_('COM_BREEZINGFORMSNG_CONFIG'),
            default => '',
        };

        if (
            in_array($act, ['managerecs', 'recordmanagement', ''], true)
            && $input->getCmd('task') === 'edit'
            && $input->getInt('record_id') > 0
        ) {
            $section .= ' / ' . $input->getInt('record_id');
        }

        $title = Text::_('COM_BREEZINGFORMSNG') . ($section !== '' ? ' / ' . $section : '');
        $document = Factory::getApplication()->getDocument();
        $document->setTitle($title);
        $document->getWebAssetManager()->addInlineStyle(
            '.icon-logo_left{
                background-image:url(' . Uri::root(true) . '/media/com_breezingformsng/images/logo_left.png);
                background-size:contain;
                background-repeat:no-repeat;
                background-position:center;
                display:inline-block;
                width:48px;
                height:48px;
                vertical-align:middle;
            }'
        );

        ToolbarHelper::title($title, 'logo_left');
    }
}
