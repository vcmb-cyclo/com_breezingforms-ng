<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

class AboutController extends BaseController
{
    private const ABOUT_LOG_FILES = ['breezingforms_install2.log', 'breezingforms_install.log'];
    private const ABOUT_LOG_TAIL_BYTES = 65536;

    public function display($cachable = false, $urlparams = [])
    {
        $application = Factory::getApplication();

        if (!$application->getIdentity()->authorise('core.manage', 'com_breezingformsng')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $application->getInput()->set('view', 'about');

        return parent::display($cachable, $urlparams);
    }

    public function showLog(): void
    {
        $this->checkToken();

        $application = Factory::getApplication();

        if (!$application->getIdentity()->authorise('core.manage', 'com_breezingformsng')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        try {
            $logReport = $this->readAboutLogReport();
            $application->setUserState('com_breezingformsng.about.log', $logReport);
            $this->setMessage(Text::sprintf('COM_BREEZINGFORMSNG_ABOUT_LOG_LOADED', (string) ($logReport['file'] ?? '')), 'message');
        } catch (\Throwable $exception) {
            $application->setUserState('com_breezingformsng.about.log', []);
            $this->setMessage(Text::sprintf('COM_BREEZINGFORMSNG_ABOUT_LOG_LOAD_FAILED', $exception->getMessage()), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_breezingformsng&view=about#bf-about-log', false));
    }

    private function readAboutLogReport(): array
    {
        $latestPath = '';
        $latestMtime = 0;

        foreach (self::ABOUT_LOG_FILES as $fileName) {
            $path = JPATH_ADMINISTRATOR . '/logs/' . $fileName;

            if (!is_file($path)) {
                continue;
            }

            $mtime = filemtime($path);

            if ($mtime === false) {
                continue;
            }

            if ($latestPath === '' || $mtime > $latestMtime) {
                $latestPath = $path;
                $latestMtime = (int) $mtime;
            }
        }

        if ($latestPath === '') {
            throw new \RuntimeException(Text::sprintf('COM_BREEZINGFORMSNG_ABOUT_LOG_NOT_FOUND', JPATH_ADMINISTRATOR . '/logs'));
        }

        if (!is_readable($latestPath)) {
            throw new \RuntimeException(basename($latestPath));
        }

        $size = filesize($latestPath);

        if ($size === false) {
            throw new \RuntimeException(basename($latestPath));
        }

        $content = '';
        $truncated = false;

        if ($size > 0) {
            $handle = fopen($latestPath, 'rb');

            if (!is_resource($handle)) {
                throw new \RuntimeException(basename($latestPath));
            }

            if ($size > self::ABOUT_LOG_TAIL_BYTES) {
                $truncated = true;
                fseek($handle, -self::ABOUT_LOG_TAIL_BYTES, SEEK_END);
            }

            $content = (string) stream_get_contents($handle);
            fclose($handle);
        }

        $loadedAt = '';

        if ($latestMtime > 0) {
            $timezone = new \DateTimeZone((string) Factory::getApplication()->get('offset', 'UTC'));
            $loadedAt = Factory::getDate('@' . $latestMtime)
                ->setTimezone($timezone)
                ->format('Y-m-d H:i:s', true);
        }

        return [
            'file' => basename($latestPath),
            'size' => $size,
            'loaded_at' => $loadedAt,
            'content' => $content,
            'truncated' => $truncated ? 1 : 0,
            'tail_bytes' => self::ABOUT_LOG_TAIL_BYTES,
        ];
    }
}
