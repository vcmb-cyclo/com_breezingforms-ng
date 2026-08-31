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
