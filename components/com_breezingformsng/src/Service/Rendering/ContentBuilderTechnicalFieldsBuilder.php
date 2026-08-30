<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds ContentBuilder record context fields submitted with a form.
 */
final class ContentBuilderTechnicalFieldsBuilder
{
    public function build(string $indentation, int $formId, int $recordId, bool $isNew): string
    {
        if ($formId === 0) {
            return '';
        }

        $fields = '<input type="hidden" name="cb_form_id" value="' . $formId . '"/>' . "\n";
        if ($recordId !== 0) {
            $fields .= '<input type="hidden" name="cb_record_id" value="' . $recordId . '"/>' . "\n";
        }
        if ($isNew) {
            $fields .= '<input type="hidden" name="cbIsNew" value="1"/>' . "\n";
        }

        return $indentation . str_replace("\n", "\n" . $indentation, rtrim($fields, "\n")) . "\n";
    }
}
