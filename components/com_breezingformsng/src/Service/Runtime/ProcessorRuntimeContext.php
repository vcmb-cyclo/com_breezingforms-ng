<?php

/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Runtime;

\defined('_JEXEC') or die;

final class ProcessorRuntimeContext
{
    public function __construct(
        public readonly RequestMetadata $requestMetadata,
        public readonly string $submitted,
        public readonly FormDisplayContext $display,
        public readonly FormPathContext $paths
    ) {
    }
}
