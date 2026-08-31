<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the optional payment method image attributes. */
final class QuickModePaymentImageBuilder
{
    public static function build(string $image, string $alt = ''): string
    {
        if ($image === '') {
            return '';
        }

        $altAttribute = $alt !== ''
            ? ' alt="' . htmlspecialchars($alt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"'
            : '';

        return 'src="' . htmlspecialchars($image, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"'
            . $altAttribute . ' ';
    }
}
