<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

use Closure;

/**
 * Builds the JavaScript that restores an editable BreezingForms record.
 *
 * The optional cleaner allows the caller to provide its runtime-specific
 * sanitization without making this service depend on Joomla.
 */
final class EditableRecordHydrationScriptBuilder
{
    public function __construct(
        private readonly ContentBuilderValueScriptBuilder $valueScriptBuilder = new ContentBuilderValueScriptBuilder(),
        private readonly ?Closure $cleanValue = null
    ) {
    }

    /**
     * @param iterable<object> $entries
     */
    public function build(iterable $entries, int $formId): string
    {
        $script = '';

        foreach ($entries as $entry) {
            $type = (string) $entry->type;
            $name = (string) $entry->name;
            $elementId = (int) $entry->element;
            $value = (string) $entry->value;
            if ($this->cleanValue !== null) {
                $value = ($this->cleanValue)($value);
            }

            switch ($type) {
                case 'Textarea':
                case 'Text':
                case 'Hidden Input':
                case 'Number Input':
                case 'Calendar':
                    $script .= $this->valueScriptBuilder->build((object) [
                        'recType' => $type,
                        'recName' => $name,
                        'recElementId' => $elementId,
                        'recValue' => $value,
                    ], $formId);
                    break;
                case 'Checkbox':
                    if ($value !== '') {
                        $script .= 'if(document.getElementById("ff_elem' . $elementId . '") && !JQuery(document.getElementById("ff_elem' . $elementId . '")).attr("checked"))JQuery(document.getElementById("ff_elem' . $elementId . '")).click();' . "\n";
                    }
                    break;
                case 'Checkbox Group':
                    $script .= $this->buildChoiceScript('checkbox', $name, $formId, $value);
                    break;
                case 'Radio Button':
                case 'Radio Group':
                    $script .= $this->buildChoiceScript('radio', $name, $formId, $value);
                    break;
                case 'Select List':
                    $script .= $this->buildSelectScript($elementId, $value);
                    break;
            }
        }

        return $script;
    }

    private function buildChoiceScript(string $type, string $name, int $formId, string $value): string
    {
        return '
							for(var i = 0;i < document.ff_form' . $formId . '.elements.length;i++){
								if(document.ff_form' . $formId . '.elements[i].type == "' . $type . '" && document.ff_form' . $formId . '.elements[i].name == "ff_nm_' . $name . '[]" && document.ff_form' . $formId . '.elements[i].value == ' . json_encode($value) . '){
									if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $formId . '.elements[i]).attr("checked")){
									    JQuery(document.ff_form' . $formId . '.elements[i]).click();
									}
								}
							}' . "\n";
    }

    private function buildSelectScript(int $elementId, string $value): string
    {
        return 'for(var i = 0; i < document.getElementById("ff_elem' . $elementId . '").options.length; i++){
								if(document.getElementById("ff_elem' . $elementId . '").options[i].value == ' . json_encode($value) . '){
									if(typeof JQuery != "undefined" && !JQuery(document.getElementById("ff_elem' . $elementId . '").options[i]).attr("selected")){
									    JQuery(document.getElementById("ff_elem' . $elementId . '").options[i]).attr("selected", true).trigger("change");
									}
								}
							}' . "\n";
    }
}
