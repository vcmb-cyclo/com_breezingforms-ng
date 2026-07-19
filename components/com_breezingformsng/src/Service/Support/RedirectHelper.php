<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Support;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;

/**
 * Enqueues an optional message and redirects the application.
 */
final class RedirectHelper
{
    public function __construct(private readonly CMSApplication $application)
    {
    }

    public function to(string $link, ?string $msg = null): void
    {
        if ($msg !== null) {
            $this->application->enqueueMessage($msg);
        }

        $this->application->redirect($link);
    }
}
