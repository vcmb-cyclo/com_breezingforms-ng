<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\View\Form;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Renders a form. The default template delegates to the native engine
 * bootstrap (breezingformsng.php), which dispatches through FormRenderer
 * service as the frontend migration progresses (MIGRATION.md, phase 8).
 */
class HtmlView extends BaseHtmlView
{
}
