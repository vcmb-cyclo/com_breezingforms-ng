<?php
/**
 * @package     BreezingForms NG
 * @copyright   Copyright (C) 2024-2026 by XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    protected $default_view = 'breezingforms';

    public function display($cachable = false, $urlparams = [])
    {
        if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_breezingforms')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        return parent::display($cachable, $urlparams);
    }
}
