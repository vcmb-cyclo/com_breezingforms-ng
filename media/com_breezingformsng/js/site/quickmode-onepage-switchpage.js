/* Extracted from BFQuickModeOnePage's inline process() script (Phase 9c
   step 2b) - the page-switching function specific to the OnePage theme's
   navigation model (all pages present in the DOM at once, switched via
   pointer-events/opacity instead of a real page reload). */
function ff_switchpage(page){
    for( var i = page; i > 0; i-- ){
        JQuery("#bfPage"+i).css("pointer-events","auto");
        JQuery("#bfPage"+i).css("opacity","1.0");
    }
    ff_currentpage = page;
    ff_initialize("pageentry");
    JQuery("#bfPage"+page).ScrollTo({offsetTop: 50});
}
