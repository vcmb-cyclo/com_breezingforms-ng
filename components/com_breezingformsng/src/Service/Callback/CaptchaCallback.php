<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Callback;

\defined('_JEXEC') or die;

use Securimage;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;

/**
 * Securimage captcha validation callback (checkCaptcha=1).
 */
final class CaptchaCallback
{
    public function __construct(
        private readonly CMSApplication $application,
        private readonly DatabaseInterface $database,
    ) {
    }

    public function check(): void
    {
        global $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $database = $this->database;

        $mainframe = $this->application;
        $db = $database;


    @ob_end_clean();

    require_once (JPATH_SITE . '/media/com_breezingformsng/images/site/captcha/securimage.php');
    $securimage = new Securimage();
    $capValue = $this->application->getInput()->getString('value', '');
    if (!$securimage->check(str_replace('?', '', $capValue))) {
        echo 'capResult=>false';
    } else {
        echo 'capResult=>true';
    }
    exit;
    }
}
