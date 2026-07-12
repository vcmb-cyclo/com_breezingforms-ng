var __bfToggleOpts = Joomla.getOptions('com_breezingformsng.admin-toggle-published') || {};

function bfTogglePublished(id, view, link) {
	var span = link.querySelector('span');
	var isPublished = span.classList.contains('icon-publish');
	var newState = isPublished ? 0 : 1;
	fetch('index.php?option=com_breezingformsng&task=' + view + '.setPublished&format=json', {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: 'id=' + id + '&state=' + newState + '&' + __bfToggleOpts.csrfToken + '=1'
	}).then(function (r) { return r.json(); }).then(function (data) {
		if (data.Result === 'OK') {
			if (newState) {
				span.classList.remove('icon-unpublish');
				span.classList.add('icon-publish');
				link.title = Joomla.Text._('JPUBLISHED');
			} else {
				span.classList.remove('icon-publish');
				span.classList.add('icon-unpublish');
				link.title = Joomla.Text._('JUNPUBLISHED');
			}
		}
	});
}
