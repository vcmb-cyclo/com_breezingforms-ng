<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/** Assembles the final hidden fields for each form execution mode. */
final class FormModeFinalizationBuilder
{
    public function frontend(
        string $context,
        string $submission,
        string $token,
        string $optional,
        string $additional,
        string $technical,
        string $routing,
        string $newline
    ): string {
        return $context . $submission . $token . $optional . $additional . $technical . $routing
            . '</form>' . $newline;
    }

    public function backend(
        string $submission,
        string $token,
        string $context,
        string $optional,
        string $technical,
        string $routing,
        string $newline
    ): string {
        return $submission . $token . $context . $optional . $technical . $routing
            . '</form>' . $newline;
    }

    public function preview(
        bool $inFrame,
        string $submission,
        string $token,
        string $context,
        string $optional,
        string $technical,
        string $routing,
        string $newline
    ): string {
        if (!$inFrame) {
            return '';
        }

        return $submission . $token . $context . $optional . $technical . $routing
            . '</form>' . $newline;
    }
}
