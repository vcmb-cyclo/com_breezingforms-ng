/* Extracted from BFQuickMode's inline process() script (Phase 9c step 2b,
   bfReCaptcha case, invisible-captcha variant). Identical between
   ClassicRenderer and BootstrapRenderer. OnePageRenderer's callback never
   reset window.bfInvisibleRecaptcha back to false on a successful check
   (it used an inline anonymous callback instead of the named
   recaptchaCheckedCallback the other two themes define) - a real,
   pre-existing behavioral difference, preserved here via
   `resetFlagOnCallback` rather than silently unified. */
function bfInitInvisibleReCaptcha(options) {
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

	function recaptchaCheckedCallback(token) {
		if (options.resetFlagOnCallback && token != '') {
			window.bfInvisibleRecaptcha = false;
		}
		if (typeof bf_htmltextareainit != 'undefined') {
			bf_htmltextareainit();
		}
		bfCallReCaptchaSubmit();
	}

	window.recaptchaExpiredCallback = function () {
		grecaptcha.reset();
	};

	window.onloadBFNewRecaptchaCallback = function () {
		grecaptcha.render('bfInvisibleReCaptchaContainer', {
			sitekey: options.sitekey,
			'expired-callback': recaptchaExpiredCallback,
			callback: recaptchaCheckedCallback,
			badge: options.badge,
			size: 'invisible'
		});
	};
}
