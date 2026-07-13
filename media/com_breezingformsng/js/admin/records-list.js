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
	fetch('index.php?option=com_breezingformsng&task=records.setFlag&format=json', {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: 'record_id=' + recordId + '&column=' + column + '&flag=' + newFlag
			+ '&' + __bfOpts.csrfToken + '=1'
	}).then(function (r) { return r.json(); }).then(function (data) {
		if (data.Result === 'OK') {
			if (newFlag) {
				span.classList.remove('icon-times', 'text-danger');
				span.classList.add('icon-check', 'text-success');
			} else {
				span.classList.remove('icon-check', 'text-success');
				span.classList.add('icon-times', 'text-danger');
			}
		}
	});
}
