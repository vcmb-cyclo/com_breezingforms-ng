<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Wraps ContentBuilder validation and hydration scripts in the loader callback.
 */
final class ContentBuilderEditableScriptWrapperBuilder
{
    public function build(int $formId, string $validationScript, string $hydrationScript): string
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
