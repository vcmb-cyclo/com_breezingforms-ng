<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Runtime;

\defined('_JEXEC') or die;

use DateInterval;
use DateTimeZone;
use Joomla\CMS\Date\Date;

final class SubmissionTimestampFormatter
{
    public function format(string $submittedAt, string $timezone): FormattedTimestamp
    {
        $date = new Date($submittedAt, new DateTimeZone($timezone));
        $offset = $date->getOffsetFromGMT();

        if ($offset > 0) {
            $date->add(new DateInterval('PT' . $offset . 'S'));
        } elseif ($offset < 0) {
            $date->sub(new DateInterval('PT' . abs($offset) . 'S'));
        }

        return new FormattedTimestamp(
            $date->format('Y-m-d H:i:s', true),
            $date->format('YmdHis', true)
        );
    }
}
