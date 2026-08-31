<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/** Wraps editable-record hydration in its loader callback. */
final class EditableRecordScriptWrapperBuilder
{
    public function build(int $formId, string $hydrationScript, string $newline): string
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
}
