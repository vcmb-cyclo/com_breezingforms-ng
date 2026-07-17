/* Extracted from BFQuickMode's inline headers() script (Phase 9c step 2b) -
   the per-field "rollover" background highlight, only loaded when the
   form's rollover property is enabled with a non-empty color (see
   ClassicRenderer::headers()). Depends on a global bfRolloverColor,
   declared inline right before this file is loaded. */
var bfElemWrapBg = "";
function bfSetElemWrapBg(){
	bfElemWrapBg = JQuery(".bfElemWrap").css("background-color");
}
function bfRollover() {
	JQuery(".ff_elem").focus(
		function(){
		    if(!JQuery(this).closest(".bfElemWrap").find(".js-calendar").is(":visible")){
			    var parent = JQuery(this).closest(".bfElemWrap");
			    parent.css("background",bfRolloverColor);
                            parent.addClass("bfRolloverBg");
                        }
		}
	).blur(
		function(){
			var parent = JQuery(this).closest(".bfElemWrap");
			parent.css("background",bfElemWrapBg);
                        parent.removeClass("bfRolloverBg");
		}
	);
}
function bfRollover2() {
	JQuery(".bfElemWrap").mouseover(
		function(e){
		    if(!JQuery(this).find(".js-calendar").is(":visible")){
			    JQuery(this).css("background",bfRolloverColor);
                            JQuery(this).addClass("bfRolloverBg");
                        }
		}
	);
	JQuery(".bfElemWrap").mouseout(
		function(e){
		    if(JQuery(e.currentTarget).hasClass("js-calendar")) return;
			JQuery(this).css("background",bfElemWrapBg);
                        JQuery(this).removeClass("bfRolloverBg");
		}
	);
}
