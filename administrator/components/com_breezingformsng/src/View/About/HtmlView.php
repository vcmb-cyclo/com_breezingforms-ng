<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\View\About;

\defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Vcmb\Component\BreezingformsNG\Administrator\View\BreezingformsNG\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        ToolbarHelper::help(
            'COM_BREEZINGFORMSNG_HELP_ABOUT_TITLE',
            false,
            Uri::base() . 'index.php?option=com_breezingformsng&task=about.display&view=about&layout=help&tmpl=component'
        );

        parent::display($tpl);
    }
}
