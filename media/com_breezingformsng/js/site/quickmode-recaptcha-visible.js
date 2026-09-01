/* Extracted from BFQuickMode's inline process() script (Phase 9c step 2b,
   bfReCaptcha case, visible/checkbox variant). Byte-identical across
   ClassicRenderer/BootstrapRenderer/OnePageRenderer except one flag:
   ClassicRenderer's grecaptcha.render() call passes a second `true`
   argument (undocumented, kept as `resetOnRerender` below since the
   other two themes never passed it and this document has no record of
   why Classic alone does). Only the sitekey/theme/size and that flag are
   dynamic; everything else (the script-already-loaded guard) was
   duplicated verbatim in all three files. */
function bfInitVisibleReCaptcha(options) {
	var onloadBFNewRecaptchaCallback = function () {
		var config = {
			sitekey: options.sitekey,
			theme: options.theme,
			size: options.size
		};
		if (options.resetOnRerender) {
			grecaptcha.render(document.getElementById('newrecaptcha'), config, true);
		} else {
			grecaptcha.render(document.getElementById('newrecaptcha'), config);
		}
	};
	window.onloadBFNewRecaptchaCallback = onloadBFNewRecaptchaCallback;

	JQuery(document).ready(function () {
		var rc_loaded = JQuery('script').filter(function () {
			return (typeof JQuery(this).attr('src') != 'undefined' && JQuery(this).attr('src').indexOf('recaptcha/api.js') > 0);
		}).length;

		if (rc_loaded === 0) {
			// historically a no-op here too: JQuery.getScript(...) was commented out in all four renderers
		}
	});
}
