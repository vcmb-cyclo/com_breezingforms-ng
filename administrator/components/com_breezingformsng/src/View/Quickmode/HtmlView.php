<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\View\Quickmode;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Vcmb\Component\BreezingformsNG\Administrator\Helper\BreadcrumbHelper;
use Vcmb\Component\BreezingformsNG\Administrator\Model\QuickmodeModel;

class HtmlView extends BaseHtmlView
{
    public string $formTitle  = '';
    public string $formName   = '';
    public string $formDesc   = '';
    public int    $formId     = 0;
    public int    $emailntf   = 1;
    public int    $published  = 1;
    public int    $debugMode  = 0;
    public string $emailadr   = '';
    public string $templateCode = '';
    public array  $elementScripts = [];
    public array  $themes         = [];
    public array  $themesBootstrap  = [];

    public function display($tpl = null): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', 1);
        $input  = Factory::getApplication()->getInput();
        $layout = $input->getCmd('layout', '');

        if ($layout === 'editor') {
            parent::display($tpl);
            return;
        }

        $model  = $this->getQuickmodeModel();
        $formId = $input->getInt('form', 0);

        $options = $formId > 0 ? $model->getFormOptions($formId) : null;

        if ($options === null) {
            $this->formName  = 'QuickForm' . mt_rand(0, mt_getrandmax());
            $this->formTitle = $this->formName;
            $this->emailntf  = 1;
            $this->emailadr  = '';
            $this->formDesc  = '';
            $this->published = 1;
            $this->debugMode = 0;
        } else {
            $this->formName  = (string) $options->name;
            $this->formTitle = (string) $options->title;
            $this->emailntf  = (int) $options->emailntf;
            $this->emailadr  = (string) $options->emailadr;
            $this->formDesc  = (string) $options->description;
            $this->published = (int) $options->published;
            $this->debugMode = (int) $options->debug_mode;
        }

        $this->formId          = $formId;
        $this->templateCode    = $formId > 0 ? $model->getTemplateCode($formId) : '';
        $this->elementScripts  = $model->getElementScripts();
        $this->themes          = $model->getThemes();
        $this->themesBootstrap = $model->getThemesBootstrap();

        // Toolbar
        $pageTitle = BreadcrumbHelper::render([
            ['label' => Text::_('COM_BREEZINGFORMSNG'), 'url' => 'index.php?option=com_breezingformsng'],
            ['label' => Text::_('COM_BREEZINGFORMSNG_MANAGEFORMS'), 'url' => 'index.php?option=com_breezingformsng&view=forms'],
            ['label' => $this->formTitle !== '' ? $this->formTitle : Text::_('COM_BREEZINGFORMSNG_INSTALLER_UNKNOWN')],
        ]);

        $doc = Factory::getApplication()->getDocument();
        $doc->setTitle(strip_tags($pageTitle));
        $doc->getWebAssetManager()->addInlineStyle(
            '.icon-logo_left{
                background-image:url(' . Uri::root(true) . '/media/com_breezingformsng/images/logo_left.png);
                background-size:contain;background-repeat:no-repeat;background-position:center;
                display:inline-block;width:48px;height:48px;vertical-align:middle;
            }'
        );
        $wa = $doc->getWebAssetManager();
        $wa->useStyle('com_breezingformsng.quickmode-style');
        $wa->useStyle('com_breezingformsng.jtree-style');
        $wa->useStyle('com_breezingformsng.admin-style');
        $wa->useStyle('com_breezingformsng.custom-style');
        $wa->useScript('com_breezingformsng.jquery-alias');
        $wa->useScript('com_breezingformsng.jtree');
        $wa->useScript('bootstrap.tab');
        $wa->useScript('com_breezingformsng.base64');
        $wa->useScript('com_breezingformsng.center');
        $wa->useScript('com_breezingformsng.scroll');
        $wa->useScript('com_breezingformsng.quickmode-elements');
        $wa->useScript('com_breezingformsng.quickmode-app');
        $wa->useScript('com_breezingformsng.quickmode-yesno-switch');
        $wa->registerAndUseScript(
            'com_breezingformsng.quickmode-form-state',
            'media/com_breezingformsng/js/admin/admin-toggle-published.js',
            ['version' => 'auto'],
            ['defer' => true],
            ['core']
        );
        $wa->useScript('com_breezingformsng.jquery-restore');
        $doc->addScriptOptions('com_breezingformsng.admin-toggle-published', [
            'csrfToken' => Session::getFormToken(),
        ]);
        Text::script('JPUBLISHED');
        Text::script('JUNPUBLISHED');
        Text::script('COM_BREEZINGFORMSNG_DEBUG_MODE_ENABLED');
        Text::script('COM_BREEZINGFORMSNG_DEBUG_MODE_DISABLED');
        Text::script('COM_BREEZINGFORMSNG_AJAX_STATE_ERROR');
        ToolbarHelper::title($pageTitle, 'logo_left');

        parent::display($tpl);
    }

    private function getQuickmodeModel(): QuickmodeModel
    {
        $model = Factory::getApplication()
            ->bootComponent('com_breezingformsng')
            ->getMVCFactory()
            ->createModel('Quickmode', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof QuickmodeModel) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }

        return $model;
    }
}
