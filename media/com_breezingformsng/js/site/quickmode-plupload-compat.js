/* Shared Plupload compatibility aliases used by all QuickMode renderers. */
(function() {
	function bfEnsurePluploadCompat() {
		if (window.moxie) {
			if (!window.mOxie) {
				window.mOxie = window.moxie;
			}
			if (!window.ctplupload) {
				window.ctplupload = {};
			}
			var imageCtor = (window.moxie.image && window.moxie.image.Image) || window.moxie.Image;
			if (imageCtor && !window.ctplupload.Image) {
				window.ctplupload.Image = imageCtor;
			}
		}
		if (window.plupload && window.plupload.Uploader && !window.plupload.Uploader.prototype.removeFileById) {
			window.plupload.Uploader.prototype.removeFileById = function(id) {
				return this.removeFile(id);
			};
		}
	}
	bfEnsurePluploadCompat();
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bfEnsurePluploadCompat);
	}
	setTimeout(bfEnsurePluploadCompat, 0);
	setTimeout(bfEnsurePluploadCompat, 500);
})();
