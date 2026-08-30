<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Selects the CAPTCHA row used by the historical validation callback.
 */
final class CaptchaValidationRowSelector
{
    /**
     * @param array<int, object> $rows
     */
    public function select(array $rows, int $rowCount): ?object
    {
        $selected = null;

        for ($index = 0; $index < $rowCount; $index++) {
            $row = $rows[$index];

            if ($row->type === 'Captcha') {
                return $row;
            }

            if ($row->type === 'ReCaptcha') {
                $selected = $row;
            }
        }

        return $selected;
    }
}
