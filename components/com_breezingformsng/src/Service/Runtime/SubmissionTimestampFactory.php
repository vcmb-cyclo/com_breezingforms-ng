<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Runtime;

\defined('_JEXEC') or die;

use DateTimeZone;
use Joomla\CMS\Date\Date;

final class SubmissionTimestampFactory
{
    public function create(string $timezone): string
    {
        return (new Date('now', new DateTimeZone($timezone)))->format('Y-m-d H:i:s');
    }
}
