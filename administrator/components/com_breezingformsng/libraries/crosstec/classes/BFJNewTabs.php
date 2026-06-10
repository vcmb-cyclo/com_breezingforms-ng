<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
/**
 * BreezingForms NG - A Joomla Forms Application
 * 
 * @version 6.0.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// use Joomla\CMS\HTML\HTMLHelper;


// shouldn't be required no longer in Joomla 3.0 Stable
require_once(JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/classes/BFBehaviorTabs.php');

class BFTabs
{

        function __construct($useCookies, $xhtml = null)
        {
        }

        static function startTab($tabText, $paneid)
        {

                // bring back in joomla 3.0 stable
                //echo HTMLHelper::_('tabs.panel', $tabText, $paneid);
                echo BFBehaviorTabs::panel($tabText, $paneid);
        }

        static function endTab()
        {
                echo '';
        }

        static function startPane($tabText)
        {
                $options = array(
                        'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
                        'useCookie' => true, // this must not be a string. Don't use quotes.
                );

                //echo HTMLHelper::_('tabs.start', 'bftab', $options);
                echo BFBehaviorTabs::start('bftab', $options);
        }

        static function endPane()
        {
                //echo HTMLHelper::_('tabs.end');
                echo BFBehaviorTabs::end();
        }
}
