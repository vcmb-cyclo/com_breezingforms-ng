document.addEventListener('DOMContentLoaded', function () {
	var yesInputs = document.querySelectorAll('input[type="radio"][id$="Yes"]');

	yesInputs.forEach(function (yesInput) {
		var baseId = yesInput.id.slice(0, -3);
		var noInput = document.getElementById(baseId + 'No');
		if (!noInput || noInput.type !== 'radio' || noInput.name !== yesInput.name) {
			return;
		}

		// Gather every node between (and including) the Yes and No radios -
		// this also swallows the raw "Oui"/"Non" text nodes sitting next to
		// them in the legacy markup, which a plain style.display toggle on
		// the inputs alone would leave behind.
		var siblings = Array.prototype.slice.call(yesInput.parentNode.childNodes);
		var startIdx = siblings.indexOf(yesInput);
		var endIdx = siblings.indexOf(noInput);
		if (startIdx === -1 || endIdx === -1 || endIdx < startIdx) {
			return;
		}
		// Also swallow the trailing "Non" text node right after the No radio.
		while (
			endIdx + 1 < siblings.length
			&& siblings[endIdx + 1].nodeType === Node.TEXT_NODE
			&& siblings[endIdx + 1].textContent.trim() !== ''
		) {
			endIdx += 1;
		}
		var toWrap = siblings.slice(startIdx, endIdx + 1);

		var hidden = document.createElement('span');
		hidden.style.display = 'none';
		yesInput.parentNode.insertBefore(hidden, yesInput);
		toWrap.forEach(function (node) {
			hidden.appendChild(node);
		});

		var wrapper = document.createElement('div');
		wrapper.className = 'form-check form-switch d-inline-block';

		var toggle = document.createElement('input');
		toggle.type = 'checkbox';
		toggle.className = 'form-check-input';
		toggle.setAttribute('role', 'switch');
		toggle.id = baseId + 'Switch';
		toggle.checked = yesInput.checked;

		wrapper.appendChild(toggle);
		hidden.insertAdjacentElement('beforebegin', wrapper);

		toggle.addEventListener('change', function () {
			var active = toggle.checked ? yesInput : noInput;
			active.checked = true;
			if (typeof active.onclick === 'function') {
				active.onclick();
			}
		});
	});
});
