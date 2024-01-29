<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
/**
* BreezingForms - A Joomla Forms Application
* @version 1.9
* @package BreezingForms
* @copyright (C) 2008-2020 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/

class BFJoomlaConfig {
    
    public static function get($name, $default = null){
        jimport('joomla.version');
        $version = new JVersion();
        if(version_compare($version->getShortVersion(), '3.0', '<')){
            return JFactory::getConfig()->getValue($name, $default);
        }else{
            return JFactory::getConfig()->get(str_replace('config.','',$name), $default);
        }
    }
}