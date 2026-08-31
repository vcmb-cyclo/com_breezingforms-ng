<?php

/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Runtime;

\defined('_JEXEC') or die;

final class SubmissionTimestampFormatter
{
    public function __construct(
        private readonly SubmissionTimestampAdjuster $timestampAdjuster = new SubmissionTimestampAdjuster()
    ) {
    }

    public function format(string $submittedAt, string $timezone): FormattedTimestamp
    {
        $date = $this->timestampAdjuster->adjust($submittedAt, $timezone);

        return new FormattedTimestamp(
            $date->format('Y-m-d H:i:s', true),
            $date->format('YmdHis', true)
        );
    }

    public function formatPattern(string $submittedAt, string $timezone, string $pattern): string
    {
        return $this->timestampAdjuster->adjust($submittedAt, $timezone)->format($pattern, true);
    }
}
