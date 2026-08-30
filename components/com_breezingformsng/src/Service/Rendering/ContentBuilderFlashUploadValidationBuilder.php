<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the shared validation callback for ContentBuilder file uploads.
 */
final class ContentBuilderFlashUploadValidationBuilder
{
    public function build(): string
    {
        return '
                                            function ff_flashupload_not_empty(element, message)
                                            {
                                                if(typeof bfSummarizers == "undefined") { '
                                                . 'alert("Flash upload validation only available in QuickMode!"); '
                                                . 'return ""}
                                                if(JQuery("#bfFlashFileQueue"+element.id.split("ff_elem")[1])'
                                                . '.html() != "" || '
                                                . 'cbFlashElemCnt[element.id] != 0 ) return "";
                                                if (message=="") message = "Please enter "+element.name+".\n";
                                                ff_validationFocus(element.name);
                                                return message;
                                            }
                                            ';
    }
}
