/* Mobile QuickMode initializer. Runs on the standard DOMContentLoaded hook
   (jQuery Mobile's pageinit/mobileinit events and its checkboxradio/
   textinput/selectmenu widget refresh calls were removed along with the
   jQuery Mobile library - see docs/maintenance/js-libraries-migration-plan.md). */
bfToggleFieldsLoaded = false;
bfSectionFieldsDeactivated = false;

document.addEventListener('DOMContentLoaded', function () {
    var JQuery = window.jQuery;

    if (!JQuery) {
        return;
    }

    if (typeof bfSetElemWrapBg != "undefined") bfSetElemWrapBg();
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
    JQuery(".tooltip").hide();
});
