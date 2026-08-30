<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the JavaScript used to restore editable ContentBuilder values.
 *
 * The caller is responsible for cleaning the value before passing the entry.
 * This service only formats the historical control updates and has no Joomla
 * or processor dependency.
 */
final class ContentBuilderValueScriptBuilder
{
    /**
     * Build the value-restoration script for one ContentBuilder entry.
     */
    public function build(object $entry, int $formId): string
    {
        $type = (string) $entry->recType;
        $name = (string) $entry->recName;
        $elementId = (int) $entry->recElementId;
        $value = (string) $entry->recValue;

        return match ($type) {
            'Textarea', 'Text', 'Number Input', 'Hidden Input', 'Calendar' =>
                $this->buildSimpleValue($entry, $name, $elementId, $value),
            'Checkbox' => $value === '' ? '' : $this->buildCheckboxValues($name, $formId, $value),
            'Checkbox Group' => $this->buildCheckboxValues($name, $formId, $value),
            'Radio Button', 'Radio Group' => $this->buildRadioValues($name, $formId, $value),
            'Select List' => $this->buildSelectValues($elementId, $value),
            default => '',
        };
    }

    private function buildSimpleValue(object $entry, string $name, int $elementId, string $value): string
    {
        $encodedValue = $this->encode($value);
        $script = 'if(typeof JQuery != "undefined"){JQuery("[name=\"ff_nm_' . $name . '[]\"]").val(' . $encodedValue . ');if(typeof JQuery != "undefined")JQuery("[name=\"ff_nm_' . $name . '[]\"]").trigger("change");}else{';
        $script .= 'if(document.getElementById("ff_elem' . $elementId . '"))document.getElementById("ff_elem' . $elementId . '").value=' . $encodedValue . ';if(typeof JQuery != "undefined")JQuery(document.getElementById("ff_elem' . $elementId . '")).trigger("change");' . "\n";
        $script .= '}';

        if ($entry->recType === 'Calendar') {
            return 'setTimeout(function(){' . $script . '}, 100);';
        }

        return $script;
    }

    private function buildCheckboxValues(string $name, int $formId, string $value): string
    {
        $script = '';
        foreach (explode(',', $value) as $selectedValue) {
            $selectedValue = trim($selectedValue);
            $script .= 'for(var i = 0;i < document.ff_form' . $formId . '.elements.length;i++){' . "\n";
            $script .= 'if(document.ff_form' . $formId . '.elements[i].type == "checkbox" && document.ff_form' . $formId . '.elements[i].name == "ff_nm_' . $name . '[]" && document.ff_form' . $formId . '.elements[i].value == ' . $this->encode($selectedValue) . '){' . "\n";
            $script .= 'if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $formId . '.elements[i]).attr("checked")){JQuery(document.ff_form' . $formId . '.elements[i]).click();}' . "\n";
            $script .= '}' . "\n";
            $script .= '}' . "\n";
        }

        return $script;
    }

    private function buildRadioValues(string $name, int $formId, string $value): string
    {
        $script = '';
        foreach (explode(',', $value) as $selectedValue) {
            $selectedValue = trim($selectedValue);
            $script .= 'for(var i = 0;i < document.ff_form' . $formId . '.elements.length;i++){' . "\n";
            $script .= 'if(document.ff_form' . $formId . '.elements[i].type == "radio" && document.ff_form' . $formId . '.elements[i].name == "ff_nm_' . $name . '[]" && document.ff_form' . $formId . '.elements[i].value == ' . $this->encode($selectedValue) . '){' . "\n";
            $script .= 'if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $formId . '.elements[i]).attr("checked")){JQuery(document.ff_form' . $formId . '.elements[i]).click();}' . "\n";
            $script .= '}' . "\n";
            $script .= '}' . "\n";
        }

        return $script;
    }

    private function buildSelectValues(int $elementId, string $value): string
    {
        $script = '';
        foreach (explode(',', $value) as $selectedValue) {
            $selectedValue = trim($selectedValue);
            $encodedValue = $this->encode($selectedValue);
            $script .= 'for(var i = 0; i < document.getElementById("ff_elem' . $elementId . '").options.length; i++){' . "\n";
            $script .= 'if(document.getElementById("ff_elem' . $elementId . '").options[i].value == ' . $encodedValue . '){' . "\n";
            $script .= 'if(typeof JQuery != "undefined" && !JQuery(document.getElementById("ff_elem' . $elementId . '").options[i]).attr("selected")){JQuery(document.getElementById("ff_elem' . $elementId . '").options[i]).attr("selected", true).trigger("change");}' . "\n";
            $script .= '}' . "\n";
            $script .= '}' . "\n";
        }

        return $script;
    }

    private function encode(string $value): string
    {
        return (string) json_encode($value);
    }
}
