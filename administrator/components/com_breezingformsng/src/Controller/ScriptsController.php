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
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Vcmb\Component\BreezingformsNG\Administrator\Service\ScriptManager;

class ScriptsController extends BaseController
{
    public function add(): void
    {
        $this->runLegacyTask('edit', []);
    }

    public function edit(): void
    {
        $this->runLegacyTask('edit');
    }

    public function save(): void
    {
        $this->assertPostToken();
        $this->runLegacyTask('save');
    }

    public function cancel(): void
    {
        $this->runLegacyTask('cancel');
    }

    public function copy(): void
    {
        $this->assertPostToken();
        $this->runLegacyTask('copy');
    }

    public function remove(): void
    {
        $this->assertPostToken();
        $this->runLegacyTask('del');
    }

    public function publish(): void
    {
        $this->assertPostToken();
        $this->runLegacyTask('publish', null, 1);
    }

    public function unpublish(): void
    {
        $this->assertPostToken();
        $this->runLegacyTask('publish', null, 0);
    }

    public function previous(): void
    {
        $this->runLegacyTask('prev');
    }

    public function next(): void
    {
        $this->runLegacyTask('next');
    }

    public function test(): void
    {
        $this->runLegacyTask('test');
    }

    private function runLegacyTask(string $method, ?array $ids = null, ?int $state = null): void
    {
        $this->assertAuthorised();
        $this->bootstrapLegacyRuntime();
        $this->prepareDocument();

        $arguments = ['com_breezingformsng', $this->getPackage()];

        if ($ids !== null || !in_array($method, ['save', 'cancel'], true)) {
            $arguments[] = $ids ?? $this->getIds();
        }

        if ($state !== null) {
            $arguments[] = $state;
        }

        $manager = new ScriptManager(
            $this->app,
            Factory::getContainer()->get(DatabaseInterface::class),
        );
        $manager->$method(...$arguments);
    }

    private function assertAuthorised(): void
    {
        if (!$this->app->getIdentity()->authorise('core.manage', 'com_breezingformsng')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    private function prepareDocument(): void
    {
        $title = Text::_('COM_BREEZINGFORMSNG') . ' / ' . Text::_('COM_BREEZINGFORMSNG_MANAGESCRIPTS');

        $document = $this->app->getDocument();
        $document->setTitle($title);
        $document->getWebAssetManager()->addInlineStyle(
            '.icon-logo_left{'
            . 'background-image:url(' . Uri::root(true) . '/media/com_breezingformsng/images/logo_left.png);'
            . 'background-size:contain;'
            . 'background-repeat:no-repeat;'
            . 'background-position:center;'
            . 'display:inline-block;'
            . 'width:48px;'
            . 'height:48px;'
            . 'vertical-align:middle;'
            . '}'
        );
        ToolbarHelper::title($title, 'logo_left');
    }

    private function assertPostToken(): void
    {
        if (!$this->checkToken('post', false)) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    private function getPackage(): string
    {
        $package = $this->app->getInput()->getString('pkg', '');

        return $package === '- blank -' ? '' : $package;
    }

    private function getIds(): array
    {
        $input = $this->app->getInput();
        $ids = (array) $input->get('ids', [], 'array');

        if ($ids === []) {
            $ids = (array) $input->get('cid', [], 'array');
        }

        return array_values(array_filter(array_map('intval', $ids)));
    }

    private function bootstrapLegacyRuntime(): void
    {
        global $ff_mospath, $ff_admpath, $ff_compath;
        global $ff_mossite, $ff_admsite, $ff_admicon, $ff_comsite;
        global $ff_config, $ff_compatible, $ff_install;
        global $database, $task;


        if (isset($ff_config)) {
            return;
        }

        $database = \Joomla\CMS\Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);

        $task       = '';
        $comppath   = '/components/com_breezingformsng';
        $ff_admpath = str_replace('\\', '/', JPATH_ADMINISTRATOR . '/components/com_breezingformsng');
        $ff_mospath = str_replace('\\', '/', dirname(dirname(dirname($ff_admpath))));
        $ff_compath = $ff_mospath . $comppath;
        $ff_admsite = $ff_mospath . '/administrator' . $comppath;
        $ff_admicon = $ff_admsite . '/images/icons';

        require_once JPATH_SITE . '/components/com_breezingformsng/src/Support/runtime_bootstrap.php';

        $ff_config = (object) [
            'areasmall'  => 4,
            'areamedium' => 12,
            'arealarge'  => 20,
            'limitdesc'  => 100,
            'piecepkg'   => '',
            'scriptpkg'  => '',
        ];
        $ff_compatible = true;
        $ff_install    = false;
    }
}
