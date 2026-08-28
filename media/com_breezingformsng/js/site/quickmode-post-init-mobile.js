/* Mobile QuickMode initializer. jQuery Mobile uses pageinit rather than the
   regular document-ready hook used by the other renderers. */
bfToggleFieldsLoaded = false;
bfSectionFieldsDeactivated = false;

JQuery(document).bind("pageinit", function() {
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
    JQuery(".bfQuickMode .hasTip").css("color", "inherit");
    JQuery(".bfQuickMode .bfTooltip").css("color", "inherit");
    JQuery("input[type=text]").bind("keypress", function(evt) {
        if (evt.keyCode == 13) {
            evt.preventDefault();
        }
    });
    JQuery(".tooltip").hide();
    setInterval(function() {
        JQuery("input[type='checkbox']").checkboxradio("refresh");
        JQuery("input[type='radio']").checkboxradio("refresh");
        JQuery("input[type='text']").textinput();
        try {
            JQuery("select").selectmenu("refresh");
        } catch (e) {}
        JQuery("textarea").textinput();
    }, 500);
});

JQuery(document).bind("mobileinit", function() {
    JQuery.mobile.loadingMessage = false;
    JQuery.mobile.ignoreContentEnabled = false;
    JQuery.mobile.selectmenu.prototype.options.nativeMenu = false;
});
