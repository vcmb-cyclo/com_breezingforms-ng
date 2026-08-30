<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the JavaScript that deactivates non-editable ContentBuilder fields.
 */
final class ContentBuilderReadonlyScriptBuilder
{
    /**
     * Build the function body used to deactivate the supplied field IDs.
     *
     * @param list<int|string> $fieldIds
     */
    public function build(array $fieldIds): string
    {
        if ($fieldIds === []) {
            return '';
        }

        $script = 'function bfContentBuilderFieldHasVisibleControl(fieldId){' . "\n";
        $script .= 'var wrap = JQuery("#bfElemWrap" + fieldId);' . "\n";
        $script .= 'if(!wrap.length){ return false; }' . "\n";
        $script .= 'var hasVisibleControl = false;' . "\n";
        $script .= 'wrap.find(".ff_elem").each(function(){' . "\n";
        $script .= 'if(typeof this.type != "undefined" && this.type != "hidden"){ hasVisibleControl = true; return false; }' . "\n";
        $script .= '});' . "\n";
        $script .= 'return hasVisibleControl;' . "\n";
        $script .= '}' . "\n";
        $script .= 'function bfDisableContentBuilderFields(){' . "\n";

        foreach ($fieldIds as $fieldId) {
            $fieldId = (string) $fieldId;
            $script .= 'if(typeof document.getElementById("ff_elem' . $fieldId . '").disabled != "undefined"){' . "\n";
            $script .= 'bfCbMainElement = document.getElementById("ff_elem' . $fieldId . '");' . "\n";
            $script .= 'bfCbRespectReadonly = (bfCbMainElement && typeof bfCbMainElement.readOnly != "undefined" && bfCbMainElement.readOnly);' . "\n";
            $script .= 'bfCbName = document.getElementById("ff_elem' . $fieldId . '").name;' . "\n";
            $script .= 'if(typeof document.getElementsByName != "undefined"){' . "\n";
            $script .= 'bfCbElements = document.getElementsByName(bfCbName);' . "\n";
            $script .= 'for(var i = 0; i < bfCbElements.length; i++){' . "\n";
            $script .= 'if(typeof bfCbElements[i].disabled != "undefined" && !bfCbRespectReadonly){' . "\n";
            $script .= 'bfCbElements[i].disabled = true;' . "\n";
            $script .= '}' . "\n";
            $script .= 'bfDeactivateField[bfCbName]=true;' . "\n";
            $script .= 'if(typeof JQuery != "undefined" && !bfContentBuilderFieldHasVisibleControl("' . $fieldId . '")){ JQuery("#bfElemWrap' . $fieldId . '").css("display", "none"); }' . "\n";
            $script .= '}' . "\n";
            $script .= '}else{' . "\n";
            $script .= 'if(!bfCbRespectReadonly){ document.getElementById("ff_elem' . $fieldId . '").disabled = true; }' . "\n";
            $script .= 'bfDeactivateField[bfCbName]=true;' . "\n";
            $script .= 'if(typeof JQuery != "undefined" && !bfContentBuilderFieldHasVisibleControl("' . $fieldId . '")){ JQuery("#bfElemWrap' . $fieldId . '").css("display", "none"); }' . "\n";
            $script .= '}' . "\n";
            $script .= '}' . "\n";
        }

        return $script . '}' . "\n";
    }
}
