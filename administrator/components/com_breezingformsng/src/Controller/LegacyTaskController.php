<?php
/**
 * @package BreezingFormsNG
 * @copyright  Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

class LegacyTaskController extends BaseController
{
    private const TOKEN_PROTECTED_TASKS = [
        'managescripts' => [
            'copy',
            'remove',
            'save',
            'publish',
            'unpublish',
        ],
        'managepieces' => [
            'copy',
            'remove',
            'save',
            'publish',
            'testrun',
            'testrunajax',
            'unpublish',
        ],
    ];

    public function assertRequestIsValid(): void
    {
        $input = Factory::getApplication()->getInput();
        $act = $input->getCmd('act', '');
        $task = $input->getCmd('task', '');

        if (!$this->requiresToken($act, $task)) {
            return;
        }

        if (Session::checkToken('post')) {
            return;
        }

        throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
    }

    private function requiresToken(string $act, string $task): bool
    {
        return isset(self::TOKEN_PROTECTED_TASKS[$act])
            && in_array($task, self::TOKEN_PROTECTED_TASKS[$act], true);
    }
}
