<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

class PieceModel extends PackageModel
{
    protected function getTableName(): string
    {
        return '#__facileforms_pieces';
    }

    protected function getSessionPrefix(): string
    {
        return 'pieces';
    }
}
