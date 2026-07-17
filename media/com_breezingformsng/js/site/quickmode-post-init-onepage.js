/* One-page QuickMode initializer. Kept separate from the Classic/Bootstrap
   initializer because this renderer has no bfSetElemWrapBg hook. */
bfToggleFieldsLoaded = false;
bfSectionFieldsDeactivated = false;

JQuery(document).ready(function() {
    if (typeof bfFade != "undefined") bfFade();
    if (typeof bfRollover != "undefined") bfRollover();
    if (typeof bfRollover2 != "undefined") bfRollover2();
    if (typeof bfRegisterToggleFields != "undefined") {
        bfRegisterToggleFields();
    } else {
        bfToggleFieldsLoaded = true;
    }
    if (typeof bfDeactivateSectionFields != "undefined") {
        bfDeactivateSectionFields();
    } else {
        bfSectionFieldsDeactivated = true;
    }
    if (JQuery.bfvalidationEngine) {
        JQuery.bfvalidationEngineLanguage.newLang();
        JQuery(".ff_elem").change(function() {
            JQuery.bfvalidationEngine.closePrompt(this);
        });
    }
    JQuery(".bfQuickMode .hasTip").css("color", "inherit");
    JQuery(".bfQuickMode .bfTooltip").css("color", "inherit");
    JQuery("input[type=text]").bind("keypress", function(evt) {
        if (evt.keyCode == 13) {
            evt.preventDefault();
        }
    });
});
