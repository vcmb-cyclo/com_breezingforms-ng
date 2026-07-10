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
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Vcmb\Component\BreezingformsNG\Administrator\Model\QuickmodeModel;

class HtmlView extends BaseHtmlView
{
    public string $formTitle  = '';
    public string $formName   = '';
    public string $formDesc   = '';
    public int    $formId     = 0;
    public int    $emailntf   = 1;
    public string $emailadr   = '';
    public string $templateCode = '';
    public array  $elementScripts = [];
    public array  $themes         = [];
    public array  $themesBootstrap  = [];
    public array  $themesBootstrap4 = [];

    public function display($tpl = null): void
    {
        $input  = Factory::getApplication()->getInput();
        $layout = $input->getCmd('layout', '');

        if ($layout === 'editor') {
            parent::display('editor');
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
        } else {
            $this->formName  = (string) $options->name;
            $this->formTitle = (string) $options->title;
            $this->emailntf  = (int) $options->emailntf;
            $this->emailadr  = (string) $options->emailadr;
            $this->formDesc  = (string) $options->description;
        }

        $this->formId          = $formId;
        $this->templateCode    = $formId > 0 ? $model->getTemplateCode($formId) : '';
        $this->elementScripts  = $model->getElementScripts();
        $this->themes          = $model->getThemes();
        $this->themesBootstrap  = $model->getThemesBootstrap();
        $this->themesBootstrap4 = $model->getThemesBootstrap4();

        // Toolbar
        $sectionTitle = Text::_('COM_BREEZINGFORMSNG_MANAGEFORMS');
        if ($this->formTitle !== '') {
            $sectionTitle .= ' / ' . htmlspecialchars($this->formTitle, ENT_QUOTES, 'UTF-8');
        }
        $pageTitle = Text::_('COM_BREEZINGFORMSNG') . ' / ' . $sectionTitle;

        $doc = Factory::getApplication()->getDocument();
        $doc->getWebAssetManager()
            ->getRegistry()
            ->addRegistryFile('administrator/components/com_breezingformsng/joomla.asset.json');
        $doc->setTitle(strip_tags($pageTitle));
        $doc->getWebAssetManager()->addInlineStyle(
            '.icon-logo_left{
                background-image:url(' . Uri::root(true) . '/media/com_breezingformsng/images/logo_left.png);
                background-size:contain;background-repeat:no-repeat;background-position:center;
                display:inline-block;width:48px;height:48px;vertical-align:middle;
            }'
        );
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
