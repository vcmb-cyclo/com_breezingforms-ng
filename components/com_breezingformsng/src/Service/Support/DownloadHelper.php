<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Support;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use RuntimeException;

/**
 * Streams a purchased file to the browser and terminates the request.
 */
final class DownloadHelper
{
    public static function stream(CMSApplication $application, string $file): never
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new RuntimeException(Text::_('COM_BREEZINGFORMSNG_DOWNLOAD_NOT_POSSIBLE'));
        }

        $stream = fopen($file, 'rb');

        if ($stream === false) {
            throw new RuntimeException(Text::_('COM_BREEZINGFORMSNG_DOWNLOAD_NOT_POSSIBLE'));
        }

        $size = filesize($file);
        $application->setHeader('Content-Description', 'File Transfer', true);
        $application->setHeader('Content-Type', 'application/octet-stream', true);
        $application->setHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"', true);
        $application->setHeader('Content-Transfer-Encoding', 'binary', true);
        $application->setHeader('Expires', '0', true);
        $application->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $application->setHeader('Pragma', 'public', true);

        if ($size !== false) {
            $application->setHeader('Content-Length', (string) $size, true);
        }

        $application->sendHeaders();

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        fpassthru($stream);
        fclose($stream);
        $application->close();
        exit;
    }
}
