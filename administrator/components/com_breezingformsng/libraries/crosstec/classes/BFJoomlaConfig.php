<?php
/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @version 6.0.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

declare(strict_types=1);

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;

/**
 * Facade kept for backward compatibility: PHP stored in the database
 * (facileforms_pieces.code, forms.piece*code) may reference this class by
 * name to read a site configuration value. No source file in this
 * repository calls it any more (Phase 9 rewrote its former callers - the
 * crosstec BFQuickMode* renderers - against native Joomla APIs), so it
 * must never be removed on the strength of a source-tree grep alone.
 */
class BFJoomlaConfig
{
    public static function get(string $name, mixed $default = null): mixed
    {
        return Factory::getApplication()->getConfig()->get(str_replace('config.', '', $name), $default);
    }
}
