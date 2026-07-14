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
use Joomla\Database\ParameterType;
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
    $optDate = HTMLHelper::date('now', 'Y-m-d H:i:s');
    $query = $database->getQuery(true)
        ->update($database->quoteName('#__facileforms_records'))
        ->set($database->quoteName('opted') . ' = 1')
        ->set($database->quoteName('opt_ip') . ' = :ip')
        ->set($database->quoteName('opt_date') . ' = :optDate')
        ->where($database->quoteName('opt_token') . ' = :token')
        ->where($database->quoteName('id') . ' = :userSubmitedID')
        ->where($database->quoteName('opted') . ' = 0')
        ->bind(':ip', $ip, ParameterType::STRING)
        ->bind(':optDate', $optDate, ParameterType::STRING)
        ->bind(':token', $token, ParameterType::STRING)
        ->bind(':userSubmitedID', $userSubmitedID, ParameterType::STRING);
    $database->setQuery($query);
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
    $optDate = HTMLHelper::date('now', 'Y-m-d H:i:s');
    $query = $database->getQuery(true)
        ->update($database->quoteName('#__facileforms_records'))
        ->set($database->quoteName('opted') . ' = 0')
        ->set($database->quoteName('opt_ip') . ' = :ip')
        ->set($database->quoteName('opt_date') . ' = :optDate')
        ->where($database->quoteName('opt_token') . ' = :token')
        ->where($database->quoteName('id') . ' = :userSubmitedID')
        ->where($database->quoteName('opted') . ' = 1')
        ->bind(':ip', $ip, ParameterType::STRING)
        ->bind(':optDate', $optDate, ParameterType::STRING)
        ->bind(':token', $token, ParameterType::STRING)
        ->bind(':userSubmitedID', $userSubmitedID, ParameterType::STRING);
    $database->setQuery($query);
    $database->execute();

    echo Text::_("COM_BREEZINGFORMSNG_FORMS_DOUBLE_OPT_OUT_EMAIL_THANK_YOU");
    }
}
