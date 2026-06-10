<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$section = $this->section;
$keys = [
    'records'       => 'RECORDS',
    'configuration' => 'CONFIGURATION',
];

$k = $keys[$section] ?? null;
?>
<div class="container-fluid p-3">
<?php if ($k !== null) : ?>
    <h1 class="h3 mb-3"><?php echo Text::_('COM_BREEZINGFORMSNG_HELP_' . $k . '_TITLE'); ?></h1>
    <p class="mb-3"><?php echo Text::_('COM_BREEZINGFORMSNG_HELP_' . $k . '_INTRO'); ?></p>
    <ul class="mb-3">
        <li><?php echo Text::_('COM_BREEZINGFORMSNG_HELP_' . $k . '_POINT_1'); ?></li>
        <li><?php echo Text::_('COM_BREEZINGFORMSNG_HELP_' . $k . '_POINT_2'); ?></li>
        <li><?php echo Text::_('COM_BREEZINGFORMSNG_HELP_' . $k . '_POINT_3'); ?></li>
    </ul>
<?php else : ?>
    <p class="text-muted"><?php echo Text::_('COM_BREEZINGFORMSNG_HELP_NOT_FOUND'); ?></p>
<?php endif; ?>
</div>
