<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the hidden fields preserving the current form routing parameters.
 */
final class FormRoutingFieldsBuilder
{
    public function build(string $return, string $template): string
    {
        $fields = '';

        if ($return !== '') {
            $fields .= '<input type="hidden" name="return" value="'
                . htmlentities($return, ENT_QUOTES, 'UTF-8') . '"/>' . "\r\n";
        }

        if ($template === 'component') {
            $fields .= '<input type="hidden" name="tmpl" value="component"/>' . "\r\n";
        }

        return $fields;
    }
}
