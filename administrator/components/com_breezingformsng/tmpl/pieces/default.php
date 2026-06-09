<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Vcmb\Component\BreezingformsNG\Administrator\View\Pieces\Renderer;

Renderer::listitems(
    $this->option,
    $this->rows,
    $this->packageList,
    $this->package,
    $this->showInternal,
    $this->search,
    $this->total,
    $this->limit,
    $this->limitStart,
    $this->pagination,
    $this->listOrder,
    $this->listDirn,
    $this->filterState
);
