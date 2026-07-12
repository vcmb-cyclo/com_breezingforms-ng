<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Callback;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;

/**
 * Double-opt-in / opt-out email confirmation endpoints.
 */
class OptCallback
{
    public function optIn(): void
    {
        global $database, $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $mainframe = Factory::getApplication();
        $db = $database;


    // DOUBLE OPT IN

    $jinput = Factory::getApplication()->getInput();
    $ip = $jinput->server->get('REMOTE_ADDR');

    $userSubmitedID = $jinput->getString('id', '');
    $token = $jinput->getString('token', '');
    $database->setQuery("UPDATE #__facileforms_records SET opted=1, opt_ip = " . $database->quote($ip) . ", opt_date = " . $database->quote(HTMLHelper::date('now', 'Y-m-d H:i:s')) . " WHERE opt_token = " . $database->quote($token) . " And id=" . $database->quote($userSubmitedID) . " And opted = 0");
    $database->execute();

    echo Text::_("COM_BREEZINGFORMSNG_FORMS_DOUBLE_OPT_EMAIL_THANK_YOU");

    // DOUBLE OPT IN END
    }

    public function optOut(): void
    {
        global $database, $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $mainframe = Factory::getApplication();
        $db = $database;


    $jinput = Factory::getApplication()->getInput();
    $ip = $jinput->server->get('REMOTE_ADDR');

    $userSubmitedID = $jinput->getString('id', '');
    $token = $jinput->getString('token', '');
    $database->setQuery("UPDATE #__facileforms_records SET opted=0, opt_ip = " . $database->quote($ip) . ", opt_date = " . $database->quote(HTMLHelper::date('now', 'Y-m-d H:i:s')) . " WHERE opt_token = " . $database->quote($token) . " And id=" . $database->quote($userSubmitedID) . " And opted = 1");
    $database->execute();

    echo Text::_("COM_BREEZINGFORMSNG_FORMS_DOUBLE_OPT_OUT_EMAIL_THANK_YOU");
    }
}
