/* Extracted from BFQuickMode's inline process() script (Phase 9c step 2b,
   bfReCaptcha case, invisible-captcha variant, Mobile theme only). Unlike
   Classic/Bootstrap/OnePage (quickmode-recaptcha-invisible.js), this theme
   builds the captcha containers dynamically via jQuery instead of echoing
   static markup, hides the field's own wrapper (jQuery Mobile lays out
   captcha differently), never defines an expired-callback, and hardcodes
   badge "inline". Preserved as-is rather than merged into the shared
   file. */
function bfInitInvisibleReCaptchaMobile(options) {
	window.bfInvisibleRecaptcha = true;

	function bfCallReCaptchaSubmit() {
		if (options.hasFlashUpload) {
			if (typeof bfAjaxObject101 == 'undefined' && typeof bfReCaptchaLoaded == 'undefined') {
				bfDoFlashUpload();
			} else {
				ff_validate_submit(this, 'click');
			}
		} else {
			ff_validate_submit(this, 'click');
		}
	}

	window.onloadBFNewRecaptchaCallback = function () {
		grecaptcha.render('bfInvisibleReCaptchaContainer', {
			sitekey: options.sitekey,
			size: 'invisible',
			theme: options.theme,
			badge: 'inline',
			callback: function () {
				if (typeof bf_htmltextareainit != 'undefined') {
					bf_htmltextareainit();
				}
				bfCallReCaptchaSubmit();
			}
		});
	};

	JQuery(document).ready(function () {
		JQuery('#bfElemWrap' + options.dbId).css('display', 'none');
		JQuery('#' + options.formId).append(
			'<div id="bfInvisibleReCaptchaContainer"></div><div id="bfInvisibleReCaptcha" class="g-recaptcha" data-callback="onloadBFNewRecaptchaCallback" data-size="invisible" data-sitekey="' + options.sitekey + '"></div>'
		);
	});
}
