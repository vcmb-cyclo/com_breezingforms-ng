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
use Joomla\Database\DatabaseInterface;
use Vcmb\Component\BreezingformsNG\Administrator\Service\DatabaseAuditService;

class AboutController extends BaseController
{
    private const ABOUT_LOG_FILES = ['breezingforms_install2.log', 'breezingforms_install.log'];
    private const ABOUT_LOG_TAIL_BYTES = 65536;
    private const CONFIGURATION_TABLES = [
        'facileforms_packages',
        'facileforms_compmenus',
        'facileforms_forms',
        'facileforms_elements',
        'facileforms_scripts',
        'facileforms_pieces',
        'facileforms_integrator_rules',
        'facileforms_integrator_items',
        'facileforms_integrator_criteria_fixed',
        'facileforms_integrator_criteria_form',
        'facileforms_integrator_criteria_joomla',
    ];

    public function display($cachable = false, $urlparams = [])
    {
        $application = Factory::getApplication();

        if (!$application->getIdentity()->authorise('core.manage', 'com_breezingformsng')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $application->getInput()->set('view', 'about');

        return parent::display($cachable, $urlparams);
    }

    public function migratePackedData(): void
    {
        $this->startRepairWorkflow();
    }

    public function startRepairWorkflow(): void
    {
        $this->checkToken();

        try {
            $app = $this->getAuthorizedApplication();
            $db = $this->getDatabase();
            $converted = 0;
            $missing = 0;

            foreach (self::CONFIGURATION_TABLES as $table) {
                if (!$this->tableExists($table)) {
                    $missing++;
                    continue;
                }

                $db->setQuery(
                    'ALTER TABLE ' . $db->quoteName('#__' . $table)
                    . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
                );
                $db->execute();
                $converted++;
            }

            $this->setMessage(Text::sprintf('COM_BREEZINGFORMSNG_ABOUT_DB_REPAIR_DONE', $converted, $missing), 'message');
        } catch (\Throwable $exception) {
            $this->setMessage(Text::sprintf('COM_BREEZINGFORMSNG_ABOUT_DB_REPAIR_FAILED', $exception->getMessage()), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_breezingformsng&task=about.display&view=about', false));
    }

    public function runAudit(): void
    {
        $this->checkToken();

        try {
            $this->getAuthorizedApplication();

            $report = (new DatabaseAuditService($this->getDatabase()))->run();
            $summary = (array) ($report['summary'] ?? []);
            Factory::getApplication()->setUserState('com_breezingformsng.about.audit', $report);

            if ((int) ($summary['issues_total'] ?? 0) === 0) {
                $this->setMessage(Text::sprintf('COM_BREEZINGFORMSNG_ABOUT_AUDIT_SUMMARY_CLEAN', $summary['scanned_tables'], $summary['total_rows']), 'message');
            } else {
                $this->setMessage(Text::sprintf('COM_BREEZINGFORMSNG_ABOUT_AUDIT_SUMMARY_ISSUES', $summary['issues_total'], $summary['scanned_tables']), 'warning');
            }
        } catch (\Throwable $exception) {
            Factory::getApplication()->setUserState('com_breezingformsng.about.audit', []);
            $this->setMessage(Text::sprintf('COM_BREEZINGFORMSNG_ABOUT_AUDIT_FAILED', $exception->getMessage()), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_breezingformsng&task=about.display&view=about', false));
    }

    public function showLog(): void
    {
        $this->checkToken();

        $application = $this->getAuthorizedApplication();

        try {
            $logReport = $this->readAboutLogReport();
            $application->setUserState('com_breezingformsng.about.log', $logReport);
            $this->setMessage(Text::sprintf('COM_BREEZINGFORMSNG_ABOUT_LOG_LOADED', (string) ($logReport['file'] ?? '')), 'message');
        } catch (\Throwable $exception) {
            $application->setUserState('com_breezingformsng.about.log', []);
            $this->setMessage(Text::sprintf('COM_BREEZINGFORMSNG_ABOUT_LOG_LOAD_FAILED', $exception->getMessage()), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_breezingformsng&task=about.display&view=about#bf-about-log', false));
    }

    public function exportConfiguration(): void
    {
        $this->checkToken();

        try {
            $app = $this->getAuthorizedApplication();
            $db = $this->getDatabase();
            $tables = [];

            foreach (self::CONFIGURATION_TABLES as $table) {
                if (!$this->tableExists($table)) {
                    continue;
                }

                $db->setQuery('SELECT * FROM ' . $db->quoteName('#__' . $table));
                $tables[$table] = $db->loadAssocList() ?: [];
            }

            $payload = [
                'component' => 'com_breezingformsng',
                'format' => 'breezingformsng-configuration',
                'version' => 1,
                'exported_at' => (new \Joomla\CMS\Date\Date())->toSql(),
                'tables' => $tables,
            ];
            $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if (!is_string($json)) {
                throw new \RuntimeException(Text::_('COM_BREEZINGFORMSNG_ABOUT_EXPORT_CONFIGURATION_INVALID'));
            }

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $fileName = 'breezingformsng-config-' . (new \Joomla\CMS\Date\Date())->format('Ymd-His') . '.json';
            $app->setHeader('Pragma', 'public', true);
            $app->setHeader('Expires', '0', true);
            $app->setHeader('Cache-Control', 'private', true);
            $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
            $app->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true);
            $app->sendHeaders();
            echo $json;
            $app->close();
        } catch (\Throwable $exception) {
            $this->setMessage(Text::sprintf('COM_BREEZINGFORMSNG_ABOUT_EXPORT_CONFIGURATION_FAILED', $exception->getMessage()), 'error');
            $this->setRedirect(Route::_('index.php?option=com_breezingformsng&task=about.display&view=about', false));
        }
    }

    public function importConfiguration(): void
    {
        $this->checkToken();
        $this->getAuthorizedApplication();
        $this->setMessage(Text::_('COM_BREEZINGFORMSNG_ABOUT_IMPORT_CONFIGURATION_PREPARE'), 'warning');
        $this->setRedirect(Route::_('index.php?option=com_breezingformsng&task=about.display&view=about', false));
    }

    private function getAuthorizedApplication()
    {
        $application = Factory::getApplication();

        if (!$application->getIdentity()->authorise('core.manage', 'com_breezingformsng')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        return $application;
    }

    private function getDatabase(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    private function tableExists(string $table): bool
    {
        $db = $this->getDatabase();

        return in_array($db->getPrefix() . $table, $db->getTableList(), true);
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
            $loadedAt = (new \Joomla\CMS\Date\Date('@' . $latestMtime))
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
