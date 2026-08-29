<?php

/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Runtime;

\defined('_JEXEC') or die;

final class FormDisplayContext
{
    public function __construct(
        public readonly int $inline,
        public readonly int $template,
        public readonly string $formId,
        public readonly string $homepage,
        public readonly bool $showGrid,
        public readonly bool $canRun
    ) {
    }
}
