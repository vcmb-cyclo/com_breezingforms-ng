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

class AboutController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        $application = Factory::getApplication();

        if (!$application->getIdentity()->authorise('core.manage', 'com_breezingformsng')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $application->getInput()->set('view', 'about');

        return parent::display($cachable, $urlparams);
    }
}
