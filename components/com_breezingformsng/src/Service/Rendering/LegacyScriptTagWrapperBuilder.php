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

/**
 * Wraps generated JavaScript in the historical
 * `<script><!-- ... //--></script>` HTML-comment-guarded tag this
 * component's rendered output has always used. Consolidates what were four
 * single-purpose, single-caller classes
 * (ContentBuilderReadonlyScriptWrapperBuilder,
 * FormValidationScriptWrapperBuilder, EditableRecordScriptWrapperBuilder,
 * ContentBuilderEditableScriptWrapperBuilder) - see
 * php-architecture-migration-plan.md, "Consolider les builders
 * d'habillage <script> triviaux".
 */
final class LegacyScriptTagWrapperBuilder
{
    public function contentBuilderReadonly(string $script, string $newline = "\n"): string
    {
        return '<script type="text/javascript">' . $newline
            . '<!--' . $newline
            . $script
            . '//-->' . $newline
            . '</script>' . $newline;
    }

    public function formValidationOpen(
        string $fileExtensionsCheck,
        string $captchaFunction,
        string $newline = "\n"
    ): string {
        return '<script type="text/javascript">' . $newline
            . '<!--' . $newline
            . $newline
            . $fileExtensionsCheck
            . $captchaFunction;
    }

    public function formValidationClose(string $newline = "\n"): string
    {
        return '//-->' . $newline . '</script>' . $newline;
    }

    public function editableRecord(int $formId, string $hydrationScript, string $newline): string
    {
        return '
				<script type="text/javascript">
                                <!--' . $newline . '
                                function bfLoadEditable(){
                                    ' . $hydrationScript . '
                                    // legacy seccode removal
                                    for(var i = 0;i < document.ff_form' . $formId . '.elements.length;i++){
                                            if(document.ff_form' . $formId . '.elements[i].name == "ff_nm_seccode[]"){
                                                    document.ff_form' . $formId . '.elements[i].value = "";
                                            }
                                    }
                                }
                                ' . $newline . '//-->
				</script>
				' . $newline;
    }

    public function contentBuilderEditable(int $formId, string $validationScript, string $hydrationScript): string
    {
        return '
                    <script type="text/javascript">
                    <!--' . "\n" . '
                    var cbFlashElemCnt = new Array();
                    function bfCheckUploadValidation(id, obj, deactivatable){
                        if(obj.checked){
                            cbFlashElemCnt[id]--;
                        }else{
                            cbFlashElemCnt[id]++;
                        }
                        if(cbFlashElemCnt[id] == 0){
                            bfDeactivateField[deactivatable]=false;
                        }else{
                            bfDeactivateField[deactivatable]=true;
                        }
                    }
                    ' . $validationScript . '
                    function bfLoadContentBuilderEditable(){
                        ' . $hydrationScript . '
                        // legacy seccode removal
                        for(var i = 0;i < document.ff_form' . $formId . '.elements.length;i++){
                                if(document.ff_form' . $formId . '.elements[i].name == "ff_nm_seccode[]"){
                                        document.ff_form' . $formId . '.elements[i].value = "";
                                }
                        }
                    }
                    ' . "\n" . '//-->
                    </script>
                    ' . "\n";
    }
}
