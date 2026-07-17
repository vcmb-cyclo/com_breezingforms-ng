<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Callback;

\defined('_JEXEC') or die;

use Securimage;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;

/**
 * Securimage captcha validation callback (checkCaptcha=1).
 */
class CaptchaCallback
{
    public function check(): void
    {
        global $database, $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $mainframe = Factory::getApplication();
        $db = $database;


    @ob_end_clean();

    require_once (JPATH_SITE . '/media/com_breezingformsng/images/site/captcha/securimage.php');
    $securimage = new Securimage();
    $capValue = Factory::getApplication()->getInput()->getString('value', '');
    if (!$securimage->check(str_replace('?', '', $capValue))) {
        echo 'capResult=>false';
    } else {
        echo 'capResult=>true';
    }
    exit;
    }
}
