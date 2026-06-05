<?php
/**
 * @package     BreezingForms
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\View\BreezingformsNG;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;

class HtmlView extends BaseHtmlView
{
    protected $modules = null;

    public function display($tpl = null)
    {
        ToolbarHelper::title('BreezingForms NG', 'logo_left');
        $doc = Factory::getApplication()->getDocument();
        $doc->setTitle("BreezingForms NG");

        // $doc->addScript( URI::root().'media/system/js/core.js' )
        // Add Joomla core JavaScript framework
 //       HTMLHelper::_('bootstrap.framework');
 //       $doc->addScript('media/system/js/core.js');

        Sidebar::addEntry(
            '<i class="fa fa-folder-open" aria-hidden="true"></i> ' . '<m>' .
            BFText::_('COM_BREEZINGFORMSNG_MANAGERECS') . '</m>',
            'index.php?option=com_breezingformsng&act=managerecs',
            BFRequest::getVar('act', '') == 'managerecs' || BFRequest::getVar('act', '') == 'recordmanagement' || BFRequest::getVar('act', '') == ''
        );

        Sidebar::addEntry(
            '<i class="fa fa-pencil-square-o" aria-hidden="true"></i> ' . '<m>' .
            BFText::_('COM_BREEZINGFORMSNG_MANAGEFORMS') . '</m>',
            'index.php?option=com_breezingformsng&act=manageforms',
            BFRequest::getVar('act', '') == 'manageforms' || BFRequest::getVar('act', '') == 'easymode' || BFRequest::getVar('act', '') == 'quickmode'
        );

        Sidebar::addEntry(
            '<i class="fa fa-code" aria-hidden="true"></i> ' . '<m>' .
            BFText::_('COM_BREEZINGFORMSNG_MANAGESCRIPTS') . '</m>',
            'index.php?option=com_breezingformsng&act=managescripts',
            BFRequest::getVar('act', '') == 'managescripts'
        );

        Sidebar::addEntry(
            '<i class="fa fa-puzzle-piece" aria-hidden="true"></i> ' . '<m>' .
            BFText::_('COM_BREEZINGFORMSNG_MANAGEPIECES') . '</m>',
            'index.php?option=com_breezingformsng&act=managepieces',
            BFRequest::getVar('act', '') == 'managepieces'
        );

        Sidebar::addEntry(
            '<i class="fa fa-link" aria-hidden="true"></i> ' . '<m>' .
            BFText::_('COM_BREEZINGFORMSNG_INTEGRATOR') . '</m>',
            'index.php?option=com_breezingformsng&act=integrate',
            BFRequest::getVar('act', '') == 'integrate'
        );

        /*
        Sidebar::addEntry('<i class="fa fa-bars" aria-hidden="true"></i> '  .'<m>'.
            BFText::_('COM_BREEZINGFORMSNG_MANAGEMENUS') .'</m>',
            'index.php?option=com_breezingformsng&act=managemenus', BFRequest::getVar('act','') == 'managemenus');*/

        Sidebar::addEntry(
            '<i class="fa fa-cog" aria-hidden="true"></i> ' . '<m>' .
            BFText::_('COM_BREEZINGFORMSNG_CONFIG') . '</m>',
            'index.php?option=com_breezingformsng&act=configuration',
            BFRequest::getVar('act', '') == 'configuration'
        );

        Sidebar::addEntry(
            '<i class="fa fa-info-circle" aria-hidden="true"></i> ' . '<m>' .
            BFText::_('COM_BREEZINGFORMSNG_ABOUT') . '</m>',
            'index.php?option=com_breezingformsng&act=about',
            BFRequest::getVar('act', '') == 'about'
        );

        $this->sidebar = '<div id="bf-sidebar">' . Sidebar::render() . '</div>';


        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript('
            jQuery(document).ready(function(){
                jQuery("#bf-sidebar").appendTo("#wrapper");
            });
            ');

        parent::display($tpl);
    }
}
