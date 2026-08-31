<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the OnePage form-submit validation callback. */
final class QuickModeSubmitValidationBuilder
{
    public function build(string $newline): string
    {
        return '                        function bf_validate_submit(element, action)' . $newline .
            '                        {' . $newline .
            '                            if(typeof bfUseErrorAlerts != "undefined"){' . $newline .
            '                             JQuery(".bfErrorMessage").html("");' . $newline .
            '                             JQuery(".bfErrorMessage").css("display","none");' . $newline .
            '                            }' . $newline .
            '                            error = ff_validation(0);' . $newline .
            $newline .
            '                            if (error != "") {' . $newline .
            $newline .
            '                                if(typeof bfUseErrorAlerts == "undefined"){' . $newline .
            '                                   alert(error);' . $newline .
            '                                } else {' . $newline .
            '                                   bfShowErrors(error);' . $newline .
            '                                }' . $newline .
            $newline .
            '                                ff_validationFocus();' . $newline .
            $newline .
            '                               JQuery(".bfErrorMessage").css("display","none");' . $newline .
            '                                ff_currentpage = ff_getPageByName(ff_validationFocusName);' . $newline .
            '                                JQuery(ff_currentpage+" .bfErrorMessage")'
            . '.css("display","block");' . $newline .
            $newline .
            '                                bf_restore_submitbutton();' . $newline .
            $newline .
            '                            } else {' . $newline .
            $newline .
            '                                ff_submitForm();' . $newline .
            '                            }' . $newline .
            '                        } // ff_validate_submit' . $newline;
    }
}
