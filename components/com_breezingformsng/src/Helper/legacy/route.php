<?php
/**
 * BreezingForms NG - A Joomla Forms Application
 * @version   1.9
 * @package   BreezingForms
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license   Released under the terms of the GNU General Public License
 **/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

require_once JPATH_ADMINISTRATOR . '/components/com_breezingformsng/libraries/crosstec/classes/BFRequest.php';

abstract class BreezingformsNGHelperRoute
{
    protected static $lookup = array();

    public static function getFormRoute($id)
    {
        $found_menu = '&found_menu=false';
        $itemid = Factory::getApplication()->getInput()->getInt('Itemid', 0);
        $the_id = explode(':', $id);
        $menu = 'Itemid';
        if (Factory::getApplication()->getConfig()->get('sef')) {
            $menu = 'menuitemid';
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $db->setQuery("Select `name` From #__facileforms_forms Where id = " . intval($the_id[0]));
            $formname = $db->loadResult();
            if ($formname) {
                $db->setQuery("Select id From #__menu Where published = 1 And link = 'index.php?option=com_breezingformsng&view=form' And ( params Like " . $db->Quote('%ff_com_name=' . $formname . '%') . " Or params Like " . $db->Quote('%"ff_com_name":"' . $formname . '"%') . " )");
                $_itemid = $db->loadResult();
                if ($_itemid) {
                    $itemid = $_itemid;
                    $menu = 'Itemid';
                    $found_menu = '&found_menu=true';
                }
            }
        }
        return 'index.php?option=com_breezingformsng&ff_form=' . $the_id[0] . '&title=' . $the_id[1] . '&' . $menu . '=' . $itemid . '&ff_applic=com_tags' . $found_menu;
    }
}
