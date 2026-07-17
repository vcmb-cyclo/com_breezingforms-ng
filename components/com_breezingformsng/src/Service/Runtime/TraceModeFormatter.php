<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Runtime;

\defined('_JEXEC') or die;

final class TraceModeFormatter
{
    public function format(mixed $mode): mixed
    {
        if (!\is_int($mode)) {
            return $mode;
        }

        $formatted = '(';

        if ($mode & _FF_TRACEMODE_FIRST) {
            $formatted .= 'first ';
        }

        $formatted .= $mode & _FF_TRACEMODE_DIRECT
            ? 'direct'
            : ($mode & _FF_TRACEMODE_APPEND ? 'append' : 'popup');

        if ($mode & _FF_TRACEMODE_DISABLE) {
            return $formatted . ' disable)';
        }

        $formatted .= match ($mode & _FF_TRACEMODE_PRIORITY) {
            0 => ' minimum',
            1 => ' low',
            2 => ' normal',
            3 => ' high',
            default => ' maximum',
        };
        $formatted .= $mode & _FF_TRACEMODE_LOCAL ? ' local' : ' global';
        $topics = $mode & _FF_TRACEMODE_TOPIC;

        if ($topics === 0) {
            $formatted .= ' none';
        } elseif ($topics === _FF_TRACEMODE_TOPIC) {
            $formatted .= ' all';
        } else {
            if ($mode & _FF_TRACEMODE_EVAL) {
                $formatted .= ' eval';
            }
            if ($mode & _FF_TRACEMODE_PIECE) {
                $formatted .= ' piece';
            }
            if ($mode & _FF_TRACEMODE_FUNCTION) {
                $formatted .= ' function';
            }
            if ($mode & _FF_TRACEMODE_MESSAGE) {
                $formatted .= ' message';
            }
        }

        return $formatted . ')';
    }
}
