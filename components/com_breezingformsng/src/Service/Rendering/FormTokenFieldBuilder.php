<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Formats the Joomla CSRF token inside the rendered form.
 */
final class FormTokenFieldBuilder
{
    public function build(string $token, string $indentation): string
    {
        return $indentation . $token . "\r\n";
    }
}
