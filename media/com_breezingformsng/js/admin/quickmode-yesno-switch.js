document.addEventListener('DOMContentLoaded', function () {
	var yesInputs = document.querySelectorAll('input[type="radio"][id$="Yes"]');

	yesInputs.forEach(function (yesInput) {
		var baseId = yesInput.id.slice(0, -3);
		var noInput = Array.prototype.find.call(
			yesInput.parentNode.querySelectorAll('input[type="radio"]'),
			function (input) {
				return input.id === baseId + 'No' && input.name === yesInput.name;
			}
		);
		if (!noInput || noInput.type !== 'radio' || noInput.name !== yesInput.name) {
			return;
		}
		var container = yesInput.parentNode;
		var label = container.querySelector('label[for="' + yesInput.id + '"]');

		// Gather every node between (and including) the Yes and No radios -
		// this also swallows the raw "Oui"/"Non" text nodes sitting next to
		// them in the legacy markup, which a plain style.display toggle on
		// the inputs alone would leave behind.
		var siblings = Array.prototype.slice.call(container.childNodes);
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
		container.insertBefore(hidden, yesInput);
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
		toggle.name = baseId + 'Switch';
		toggle.checked = yesInput.checked;

		if (label) {
			label.id = baseId + 'Label';
			label.htmlFor = toggle.id;
			toggle.setAttribute('aria-labelledby', label.id);
		}

		wrapper.appendChild(toggle);
		hidden.insertAdjacentElement('beforebegin', wrapper);

		toggle.addEventListener('change', function () {
			yesInput.checked = toggle.checked;
			noInput.checked = !toggle.checked;
			yesInput.toggleAttribute('checked', toggle.checked);
			noInput.toggleAttribute('checked', !toggle.checked);
		});

		function syncToggle() {
			toggle.checked = yesInput.checked;
		}

		yesInput.addEventListener('change', syncToggle);
		noInput.addEventListener('change', syncToggle);
		window.addEventListener('load', syncToggle, { once: true });

		// QuickMode restores property values with the bundled jQuery 1.3.2
		// (attr/prop shims assign the DOM property directly): no change event
		// fires and no attribute mutates, so neither listeners nor a
		// MutationObserver see it. Hook the checked property setter on both
		// radios so every programmatic assignment resyncs the switch.
		var checkedDescriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'checked');
		[yesInput, noInput].forEach(function (input) {
			Object.defineProperty(input, 'checked', {
				configurable: true,
				get: function () {
					return checkedDescriptor.get.call(this);
				},
				set: function (value) {
					checkedDescriptor.set.call(this, value);
					syncToggle();
				}
			});
		});
	});
});
