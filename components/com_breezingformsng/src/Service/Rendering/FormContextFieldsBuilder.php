<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the hidden form-context fields shared by the execution modes.
 */
final class FormContextFieldsBuilder
{
    /**
     * @param array<string, int|string> $fields
     */
    public function build(array $fields, string $indentation, string $newline = "\r\n"): string
    {
        $output = '';

        foreach ($fields as $name => $value) {
            $output .= $indentation . '<input type="hidden" name="'
                . htmlentities($name, ENT_QUOTES, 'UTF-8') . '" value="'
                . htmlentities((string) $value, ENT_QUOTES, 'UTF-8') . '"/>' . $newline;
        }

        return $output;
    }
}
