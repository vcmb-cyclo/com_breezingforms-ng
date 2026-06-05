<?php
/**
 * BreezingForms NG - A Joomla Forms Application
 * 
 * @version 6.0.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2006 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 **/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;

class BFJoomlaConfig
{

    public static function get($name, $default = null)
    {
        return Factory::getConfig()->get(str_replace('config.', '', $name), $default);
    }
}