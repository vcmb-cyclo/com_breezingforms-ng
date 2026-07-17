<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Support;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Enqueues an optional message and redirects the application.
 */
final class RedirectHelper
{
    public static function to(string $link, ?string $msg = null): void
    {
        $app = Factory::getApplication();

        if ($msg !== null) {
            $app->enqueueMessage($msg);
        }

        $app->redirect($link);
    }
}
