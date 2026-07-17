/* Extracted from BFQuickMode's inline headers() script (Phase 9c step 2b) -
   the "fade in" page-load effect, only loaded when the form's fadeIn
   property is enabled (see ClassicRenderer::headers()). */
function bfFade(){
	JQuery(".bfPageIntro").fadeIn(1000);
	var size = 0;
	JQuery(".bfFadingClass").each(function(i,val){
		var t = this;
		setTimeout(function(){JQuery(t).fadeIn(1000)}, (i*100));
		size = i;
	});
	setTimeout('JQuery(".bfSubmitButton").fadeIn(1000)', size * 100);
	setTimeout('JQuery(".bfPrevButton").fadeIn(1000)', size * 100);
	setTimeout('JQuery(".bfNextButton").fadeIn(1000)', size * 100);
	setTimeout('JQuery(".bfCancelButton").fadeIn(1000)', size * 100);
}
