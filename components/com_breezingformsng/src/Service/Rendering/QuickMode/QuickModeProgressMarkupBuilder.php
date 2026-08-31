<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the shared Bootstrap QuickMode progress widget. */
final class QuickModeProgressMarkupBuilder
{
    public static function build(mixed $progressClass, mixed $barClass, bool $lastPageThankYou): string
    {
        return '<div class="' . (string) $progressClass . '"><div id="bfProgressBar" class="'
            . (string) $barClass . '"></div></div>
                        <script type="text/javascript">
                        <!--
                        function bfUpdateProgress(){
                            if(ff_currentpage > 1){
                                var pages = JQuery(".bfPage").size()' . ($lastPageThankYou ? '-1' : '') . ';
                                var result = Math.round(((ff_currentpage-1) / pages)*100);
                                JQuery("#bfProgressBar").css("width",result+"%");
                            }else{
                                JQuery("#bfProgressBar").css("width","0%");
                            }
                        }
                        JQuery(document).ready(function(){
                            setInterval("bfUpdateProgress()", 500);
                        });
                        -->
                        </script>';
    }
}
