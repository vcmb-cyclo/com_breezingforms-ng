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

/** Applies the historical timezone adjustment used by runtime timestamps. */
final class SubmissionTimestampAdjuster
{
    public function adjust(string $submittedAt, string $timezone): Date
    {
        $date = new Date($submittedAt, new DateTimeZone($timezone));
        $offset = $date->getOffsetFromGMT();

        if ($offset > 0) {
            $date->add(new DateInterval('PT' . $offset . 'S'));
        } elseif ($offset < 0) {
            $date->sub(new DateInterval('PT' . abs($offset) . 'S'));
        }

        return $date;
    }
}
