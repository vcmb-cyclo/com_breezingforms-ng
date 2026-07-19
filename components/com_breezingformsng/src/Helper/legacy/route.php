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
use Joomla\Database\ParameterType;

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
            $formId = (int) $the_id[0];
            $query = $db->getQuery(true)
                ->select($db->quoteName('name'))
                ->from($db->quoteName('#__facileforms_forms'))
                ->where($db->quoteName('id') . ' = :formId')
                ->bind(':formId', $formId, ParameterType::INTEGER);
            $db->setQuery($query);
            $formname = $db->loadResult();
            if ($formname) {
                $likeEquals = '%ff_com_name=' . $formname . '%';
                $likeJson = '%"ff_com_name":"' . $formname . '"%';
                $query = $db->getQuery(true)
                    ->select('id')
                    ->from($db->quoteName('#__menu'))
                    ->where($db->quoteName('published') . ' = 1')
                    ->where($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_breezingformsng&view=form'))
                    ->extendWhere('AND', [
                        $db->quoteName('params') . ' LIKE :likeEquals',
                        $db->quoteName('params') . ' LIKE :likeJson',
                    ], 'OR')
                    ->bind(':likeEquals', $likeEquals, ParameterType::STRING)
                    ->bind(':likeJson', $likeJson, ParameterType::STRING);
                $db->setQuery($query);
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
