var __bfOpts = Joomla.getOptions('com_breezingformsng.records-list') || {};

Joomla.submitbutton = function (task) {
	var form = document.getElementById('adminForm');
	if (task === 'records.remove') {
		if (!confirm(Joomla.Text._('COM_BREEZINGFORMSNG_CONFIRM_DELETE_RECORDS'))) {
			return false;
		}
	}
	form.querySelector('input[name="task"]').value = task;
	form.submit();
	return true;
};

document.addEventListener('DOMContentLoaded', function () {
	var markToggle = document.querySelector('#toolbar-mark-options button.button-mark-options');
	var boxchecked = document.querySelector('#adminForm input[name="boxchecked"]');
	if (!markToggle || !boxchecked) {
		return;
	}
	var updateMarkToggle = function () {
		markToggle.disabled = parseInt(boxchecked.value, 10) === 0;
	};
	boxchecked.addEventListener('change', updateMarkToggle);
	updateMarkToggle();
});

function bfToggleFlag(recordId, column, link) {
	var span = link.querySelector('span');
	var isChecked = span.classList.contains('icon-check');
	var newFlag = isChecked ? 0 : 1;
	var params = new URLSearchParams();
	params.append('record_id', recordId);
	params.append('column', column);
	params.append('flag', newFlag);
	if (__bfOpts && __bfOpts.csrfToken) {
		params.append(__bfOpts.csrfToken, 1);
	}

	fetch('index.php?option=com_breezingformsng&task=records.setFlag&format=json', {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
		body: params.toString()
	}).then(function (response) {
		if (!response.ok) {
			throw new Error('HTTP ' + response.status);
		}

		return response.json();
	}).then(function (data) {
		if (data && data.data) {
			data = typeof data.data === 'string' ? JSON.parse(data.data) : data.data;
		}

		if (data.Result === 'OK') {
			if (newFlag) {
				span.classList.remove('icon-times', 'text-danger');
				span.classList.add('icon-check', 'text-success');
			} else {
				span.classList.remove('icon-check', 'text-success');
				span.classList.add('icon-times', 'text-danger');
			}
		} else {
			throw new Error(data.Message || 'Invalid response');
		}
	}).catch(function () {
		Joomla.renderMessages({ error: [Joomla.Text._('COM_BREEZINGFORMSNG_AJAX_STATE_ERROR')] });
	});
}
