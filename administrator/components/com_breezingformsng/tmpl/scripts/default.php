<?php
/**
 * @package     BreezingForms NG
 * @copyright  Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_breezingformsng/admin/script.html.php';

HTML_facileFormsScript::listitems(
    $this->option,
    $this->rows,
    $this->packageList,
    $this->package,
    $this->search,
    $this->total,
    $this->limit,
    $this->limitStart,
    $this->pageSizes,
    $this->pagination
);
