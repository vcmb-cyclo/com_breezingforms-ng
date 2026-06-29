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
use Joomla\CMS\HTML\Helpers\Sidebar;
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

        Sidebar::addEntry(
            '<i class="fa fa-folder-open" aria-hidden="true"></i> ' . '<m>' .
            \BFText::_('COM_BREEZINGFORMSNG_MANAGERECS') . '</m>',
            'index.php?option=com_breezingformsng&view=records',
            \BFRequest::getVar('view', '') == 'records'
            || \BFRequest::getVar('act', '') == 'managerecs'
            || \BFRequest::getVar('act', '') == 'recordmanagement'
            || (\BFRequest::getVar('act', '') == '' && \BFRequest::getVar('view', '') == '')
        );

        Sidebar::addEntry(
            '<i class="fa fa-pencil-square-o" aria-hidden="true"></i> ' . '<m>' .
            \BFText::_('COM_BREEZINGFORMSNG_MANAGEFORMS') . '</m>',
            'index.php?option=com_breezingformsng&act=manageforms',
            \BFRequest::getVar('act', '') == 'manageforms' || \BFRequest::getVar('act', '') == 'easymode' || \BFRequest::getVar('act', '') == 'quickmode'
        );

        Sidebar::addEntry(
            '<i class="fa fa-code" aria-hidden="true"></i> ' . '<m>' .
            \BFText::_('COM_BREEZINGFORMSNG_MANAGESCRIPTS') . '</m>',
            'index.php?option=com_breezingformsng&view=scripts',
            \BFRequest::getVar('act', '') == 'managescripts' || \BFRequest::getVar('view', '') == 'scripts'
        );

        Sidebar::addEntry(
            '<i class="fa fa-puzzle-piece" aria-hidden="true"></i> ' . '<m>' .
            \BFText::_('COM_BREEZINGFORMSNG_MANAGEPIECES') . '</m>',
            'index.php?option=com_breezingformsng&view=pieces',
            \BFRequest::getVar('act', '') == 'managepieces' || \BFRequest::getVar('view', '') == 'pieces'
        );

        Sidebar::addEntry(
            '<i class="fa fa-link" aria-hidden="true"></i> ' . '<m>' .
            \BFText::_('COM_BREEZINGFORMSNG_INTEGRATOR') . '</m>',
            'index.php?option=com_breezingformsng&act=integrate',
            \BFRequest::getVar('act', '') == 'integrate'
        );

        /*
        Sidebar::addEntry('<i class="fa fa-bars" aria-hidden="true"></i> '  .'<m>'.
            \BFText::_('COM_BREEZINGFORMSNG_MANAGEMENUS') .'</m>',
            'index.php?option=com_breezingformsng&act=managemenus', \BFRequest::getVar('act','') == 'managemenus');*/

        Sidebar::addEntry(
            '<i class="fa fa-cog" aria-hidden="true"></i> ' . '<m>' .
            \BFText::_('COM_BREEZINGFORMSNG_CONFIG') . '</m>',
            'index.php?option=com_breezingformsng&act=configuration',
            \BFRequest::getVar('act', '') == 'configuration'
        );

        Sidebar::addEntry(
            '<i class="fa fa-info-circle" aria-hidden="true"></i> ' . '<m>' .
            \BFText::_('COM_BREEZINGFORMSNG_ABOUT') . '</m>',
            'index.php?option=com_breezingformsng&task=about.display&view=about',
            \BFRequest::getVar('view', '') == 'about'
        );

        $this->sidebar = '<div id="bf-sidebar">' . Sidebar::render() . '</div>';


        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript('
            jQuery(document).ready(function(){
                jQuery("#bf-sidebar").appendTo("#wrapper");
            });
            ');

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
            $act === 'manageforms', $act === 'easymode', $act === 'quickmode' => \BFText::_('COM_BREEZINGFORMSNG_MANAGEFORMS'),
            $act === 'integrate' => \BFText::_('COM_BREEZINGFORMSNG_INTEGRATOR'),
            $act === 'configuration' => \BFText::_('COM_BREEZINGFORMSNG_CONFIG'),
            $view === 'about' => \BFText::_('COM_BREEZINGFORMSNG_ABOUT'),
            default => '',
        };
    }
}
