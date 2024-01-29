<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
/**
* BreezingForms - A Joomla Forms Application
* @version 1.9
* @package BreezingForms
* @copyright (C) 2008-2020 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/

jimport('joomla.version');
$version = new JVersion();

if (version_compare($version->getShortVersion(), '3.0', '>=')) {

    require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_breezingforms'.DS.'libraries'.DS.'crosstec'.DS.'classes'.DS.'BFJNewTabs.php');

} else {
    
    JLoader::register('JPaneTabs', JPATH_LIBRARIES.DS.'joomla'.DS.'html'.DS.'pane.php');
    
    class BFTabs extends JPaneTabs {
            var $useCookies = false;

            function __construct( $useCookies, $xhtml = null) {

                    parent::__construct( array('useCookies' => $useCookies) );
            }

            function startTab( $tabText, $paneid ) {
                    echo $this->startPanel( $tabText, $paneid);
            }

            function endTab() {
                    echo $this->endPanel();
            }

            function startPane( $tabText ){
                    echo parent::startPane( $tabText );
            }

            function endPane(){
                    echo parent::endPane();
            }
    }
}