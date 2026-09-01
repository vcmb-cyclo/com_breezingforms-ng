<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

class ScriptModel extends PackageModel
{
    protected function getTableName(): string
    {
        return '#__facileforms_scripts';
    }

    protected function getSessionPrefix(): string
    {
        return 'scripts';
    }
}
