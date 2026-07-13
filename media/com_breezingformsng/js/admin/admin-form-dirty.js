var __bfDirtyInitialState = '';
var __bfDirtySubmitting = false;
var __bfDirtySubmitbutton = Joomla.submitbutton;

function bfDirtyFormState(form) {
	return new URLSearchParams(new FormData(form)).toString();
}

function bfDirtyIsChanged(form) {
	return bfDirtyFormState(form) !== __bfDirtyInitialState;
}

function bfDirtySyncSaveButton(form) {
	if (!__bfOpts.saveTask) {
		return;
	}

	var button = document.querySelector('joomla-toolbar-button[task="' + __bfOpts.saveTask + '"] button, [onclick*="' + __bfOpts.saveTask + '"]');
	if (!button) {
		return;
	}

	if (button.dataset.bfOriginalTitle === undefined) {
		button.dataset.bfOriginalTitle = button.title;
		button.dataset.bfOriginalTabindex = button.getAttribute('tabindex') || '';
	}

	var dirty = bfDirtyIsChanged(form);
	button.classList.toggle('disabled', !dirty);
	button.setAttribute('aria-disabled', dirty ? 'false' : 'true');
	button.style.pointerEvents = dirty ? '' : 'none';
	button.title = dirty ? button.dataset.bfOriginalTitle : Joomla.Text._('COM_BREEZINGFORMSNG_TEST_NO_CHANGES');

	if (dirty) {
		if (button.dataset.bfOriginalTabindex === '') {
			button.removeAttribute('tabindex');
		} else {
			button.setAttribute('tabindex', button.dataset.bfOriginalTabindex);
		}
	} else {
		button.tabIndex = -1;
	}
}

Joomla.submitbutton = function (task) {
	var form = document.getElementById('adminForm');

	if (__bfOpts.cancelTask && task === __bfOpts.cancelTask && bfDirtyIsChanged(form)
		&& !confirm(Joomla.Text._('COM_BREEZINGFORMSNG_CONFIRM_DISCARD_CHANGES'))) {
		return false;
	}

	return __bfDirtySubmitbutton(task);
};

document.addEventListener('DOMContentLoaded', function () {
	var form = document.getElementById('adminForm');
	if (!form) {
		return;
	}

	__bfDirtyInitialState = bfDirtyFormState(form);
	form.addEventListener('breezingformsng:form-submit', function () {
		__bfDirtySubmitting = true;
	});
	form.addEventListener('input', function () { bfDirtySyncSaveButton(form); });
	form.addEventListener('change', function () { bfDirtySyncSaveButton(form); });
	bfDirtySyncSaveButton(form);

	window.addEventListener('beforeunload', function (event) {
		if (!__bfDirtySubmitting && bfDirtyIsChanged(form)) {
			event.preventDefault();
			event.returnValue = '';
		}
	});
});
