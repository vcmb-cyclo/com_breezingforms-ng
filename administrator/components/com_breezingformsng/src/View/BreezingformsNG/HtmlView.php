<?php
/**
 * @package BreezingFormsNG
 * @author      Markus Bopp
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\View\BreezingformsNG;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
    protected $modules = null;

    public function display($tpl = null)
    {
        $doc = Factory::getApplication()->getDocument();
        $doc->setTitle($this->getPageTitle());
        $doc->getWebAssetManager()->addInlineStyle(
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

        ToolbarHelper::title($this->getToolbarTitle(), 'logo_left');

        // $doc->addScript( URI::root().'media/system/js/core.js' )
        // Add Joomla core JavaScript framework
 //       HTMLHelper::_('bootstrap.framework');
 //       $doc->addScript('media/system/js/core.js');

        parent::display($tpl);
    }

    private function getPageTitle(): string
    {
        return trim(strip_tags($this->getToolbarTitle()));
    }

    private function getToolbarTitle(): string
    {
        $section = $this->getToolbarSectionTitle();

        if ($section === '') {
            return Text::_('COM_BREEZINGFORMSNG');
        }

        return Text::_('COM_BREEZINGFORMSNG') . ' / ' . $section;
    }

    private function getToolbarSectionTitle(): string
    {
        $input = Factory::getApplication()->getInput();
        $view = $input->getCmd('view', '');
        $act = $input->getCmd('act', '');

        return match (true) {
            $view === 'scripts', $act === 'managescripts' => \BFText::_('COM_BREEZINGFORMSNG_MANAGESCRIPTS'),
            $view === 'pieces', $act === 'managepieces' => \BFText::_('COM_BREEZINGFORMSNG_MANAGEPIECES'),
            $view === 'records', $act === 'managerecs', $act === 'recordmanagement' => \BFText::_('COM_BREEZINGFORMSNG_RECORDS_SECTION_TITLE'),
	            $view === 'forms', $act === 'manageforms', $act === 'quickmode' => \BFText::_('COM_BREEZINGFORMSNG_MANAGEFORMS'),
            $view === 'menus', $act === 'managemenus' => \BFText::_('COM_BREEZINGFORMSNG_MANAGEMENUS'),
            $act === 'integrate' => \BFText::_('COM_BREEZINGFORMSNG_INTEGRATOR'),
            $act === 'configuration' => \BFText::_('COM_BREEZINGFORMSNG_CONFIG'),
            $view === 'about' => \BFText::_('COM_BREEZINGFORMSNG_ABOUT'),
            default => '',
        };
    }
}
