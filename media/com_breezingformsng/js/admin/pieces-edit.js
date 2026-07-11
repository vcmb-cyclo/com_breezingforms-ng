var __bfOpts = Joomla.getOptions('com_breezingformsng.pieces-edit') || {};

function checkIdentifier(value)
						{
							var invalidChars = /\W/;
							var error = '';
							if (value == '')
								error += Joomla.Text._('COM_BREEZINGFORMSNG_PIECES_ENTERNAME') + "\n";
							else
			if (invalidChars.test(value))
				error += Joomla.Text._('COM_BREEZINGFORMSNG_PIECES_ENTERIDENT') + "\n";
			return error;
						} // checkIdentifier

			function submitbutton(pressbutton) {
				var form = document.adminForm;
				var error = '';
				if ((pressbutton == 'test' || pressbutton == 'prev' || pressbutton == 'next') && isEditTestBlocked()) {
					alert(Joomla.Text._('COM_BREEZINGFORMSNG_TEST_SAVE_PIECE_BEFORE_CONTINUE'));
					return;
				}
				if (pressbutton != 'cancel' && pressbutton != 'test' && pressbutton != 'prev' && pressbutton != 'next') {
					error += checkIdentifier(form.name.value, 'name');
					if (form.title.value == '') error += Joomla.Text._('COM_BREEZINGFORMSNG_PIECES_ENTTITLE') + "\n";
				} // if
				if (error != '')
					alert(error);
				else
					var task = pressbutton === 'new' ? 'add' : (pressbutton === 'prev' ? 'previous' : pressbutton);
					Joomla.submitform('pieces.' + task);
			} // submitbutton
			Joomla.submitbutton = submitbutton;
			window.submitbutton = submitbutton;

			onload = function () {
				document.adminForm.title.focus();
			} // onload

			function getEditTestToolbarButton() {
				return findToolbarButton('test', ['test']);
			}

			function getEditTestToolbarButtons() {
				return findToolbarButtons('test', ['test']);
			}

			function getEditSaveToolbarButton() {
				return findToolbarButton('save', ['save', 'enregistrer']);
			}

			function getEditPrevToolbarButton() {
				return findToolbarButton('prev', ['prev', 'precedent']);
			}

			function getEditNextToolbarButton() {
				return findToolbarButton('next', ['next', 'suivant']);
			}

			function findToolbarButton(taskName, textHints) {
				var buttons = findToolbarButtons(taskName, textHints);
				return buttons.length ? buttons[0] : null;
			}

			function findToolbarButtons(taskName, textHints) {
				var toolbarRoots = document.querySelectorAll('#toolbar, .toolbar, .subhead, .btn-toolbar, joomla-toolbar, .joomla-toolbar-button');
				var matches = [];
				var seen = [];
				function pushMatch(node) {
					if (!node || node.id === 'bf-edit-piece-unit-tests-button') {
						return;
					}
					var target = node.closest ? (node.closest('button, a, [role="button"], li, .btn, .toolbar-item') || node) : node;
					if (seen.indexOf(target) === -1) {
						seen.push(target);
						matches.push(target);
					}
				}
				var selectors = [
					'#toolbar-' + taskName,
					'.toolbar-' + taskName,
					'.button-' + taskName,
					'[data-task="' + taskName + '"]',
					"[onclick*='" + taskName + "']",
					"[href*='" + taskName + "']"
				];
				for (var r = 0; r < toolbarRoots.length; r++) {
					for (var s = 0; s < selectors.length; s++) {
						var scopedMatches = toolbarRoots[r].querySelectorAll(selectors[s]);
						for (var m = 0; m < scopedMatches.length; m++) {
							pushMatch(scopedMatches[m]);
						}
					}
				}
				for (var s = 0; s < selectors.length; s++) {
					var directMatches = document.querySelectorAll(selectors[s]);
					for (var d = 0; d < directMatches.length; d++) {
						pushMatch(directMatches[d]);
					}
				}
				var candidates = document.querySelectorAll('#toolbar button, #toolbar a, .toolbar button, .toolbar a, .subhead button, .subhead a, .btn-toolbar button, .btn-toolbar a, joomla-toolbar button, joomla-toolbar a, .joomla-toolbar-button button, .joomla-toolbar-button a');
				if (!candidates.length) {
					candidates = document.querySelectorAll('button, a, [role="button"]');
				}
				for (var i = 0; i < candidates.length; i++) {
					var candidate = candidates[i];
					var haystack = [
						candidate.id || '',
						candidate.className || '',
						candidate.getAttribute('data-task') || '',
						candidate.getAttribute('onclick') || '',
						candidate.getAttribute('href') || '',
						candidate.textContent || '',
						candidate.title || '',
						candidate.getAttribute('aria-label') || ''
					].join(' ').toLowerCase();
					if (haystack.indexOf(taskName.toLowerCase()) !== -1) {
						pushMatch(candidate);
					}
					for (var h = 0; h < textHints.length; h++) {
						if (haystack.indexOf(String(textHints[h]).toLowerCase()) !== -1) {
							pushMatch(candidate);
						}
					}
				}
				return matches;
			}

			function normalizeValue(value) {
				return String(value || '').replace(/\r\n/g, '\n').replace(/\r/g, '\n');
			}

			function getCodeMirrorInstance(field) {
				if (!field || !field.parentNode) {
					return null;
				}
				var sibling = field.nextElementSibling;
				while (sibling) {
					if (sibling.CodeMirror) {
						return sibling.CodeMirror;
					}
					sibling = sibling.nextElementSibling;
				}
				var wrappers = field.parentNode.querySelectorAll('.CodeMirror');
				for (var i = 0; i < wrappers.length; i++) {
					if (field.compareDocumentPosition(wrappers[i]) & Node.DOCUMENT_POSITION_FOLLOWING) {
						return wrappers[i].CodeMirror || null;
					}
				}
				return null;
			}

			function getFieldValue(fieldId) {
				var field = document.getElementById(fieldId);
				if (!field && document.adminForm && document.adminForm.elements && document.adminForm.elements[fieldId]) {
					field = document.adminForm.elements[fieldId];
				}
				if (!field) {
					return '';
				}
				if (typeof RadioNodeList !== 'undefined' && field instanceof RadioNodeList) {
					return field.value;
				}
				if (field.length && typeof field.value !== 'undefined' && !field.tagName) {
					return field.value;
				}
				if (window.Joomla && Joomla.editors && Joomla.editors.instances && Joomla.editors.instances[fieldId] && typeof Joomla.editors.instances[fieldId].getValue === 'function') {
					return Joomla.editors.instances[fieldId].getValue();
				}
				if (typeof field.value !== 'undefined') {
					var codeMirrorInstance = getCodeMirrorInstance(field);
					if (codeMirrorInstance) {
						return codeMirrorInstance.getValue();
					}
					return field.value;
				}
				return '';
			}

			function getCurrentEditState() {
				return {
					title: getFieldValue('title'),
					type: getFieldValue('type'),
					package: getFieldValue('package'),
					name: getFieldValue('name'),
					published: getFieldValue('published'),
					description: getFieldValue('description'),
					code: getFieldValue('code'),
					unit_tests: getFieldValue('unit_tests')
				};
			}

			function isEditDirty() {
				var initialState = __bfOpts.initialState || {};
				var currentState = getCurrentEditState();
				var keys = Object.keys(initialState);
				for (var i = 0; i < keys.length; i++) {
					var key = keys[i];
					if (normalizeValue(currentState[key]) !== normalizeValue(initialState[key])) {
						return true;
					}
				}
				return false;
			}

			function isEditTestBlocked() {
				return isEditDirty();
			}

			function syncEditSaveToolbarButton() {
				var button = getEditSaveToolbarButton();
				if (!button) {
					return;
				}
				var isDirty = isEditDirty();
				button.classList.toggle('disabled', !isDirty);
				button.setAttribute('aria-disabled', isDirty ? 'false' : 'true');
				button.style.pointerEvents = isDirty ? '' : 'none';
				button.style.opacity = isDirty ? '' : '0.5';
				if (button.tagName === 'BUTTON') {
					button.disabled = !isDirty;
				}
				if (!isDirty) {
					button.setAttribute('tabindex', '-1');
					button.title = Joomla.Text._('COM_BREEZINGFORMSNG_TEST_NO_CHANGES');
				} else {
					button.removeAttribute('tabindex');
					button.title = '';
				}
			}

			function syncEditTestToolbarButton() {
				var buttons = getEditTestToolbarButtons();
				if (!buttons.length) {
					return;
				}
				var isBlocked = isEditTestBlocked();
				for (var i = 0; i < buttons.length; i++) {
					var button = buttons[i];
					button.classList.toggle('disabled', isBlocked);
					button.setAttribute('aria-disabled', isBlocked ? 'true' : 'false');
					button.style.pointerEvents = isBlocked ? 'none' : '';
					button.style.opacity = isBlocked ? '0.5' : '';
					if (button.tagName === 'BUTTON') {
						button.disabled = isBlocked;
					}
					if (isBlocked) {
						button.setAttribute('tabindex', '-1');
						button.title = Joomla.Text._('COM_BREEZINGFORMSNG_TEST_SAVE_PIECE_BEFORE_TESTS');
					} else {
						button.removeAttribute('tabindex');
						button.title = '';
					}
				}
			}

			function syncEditNavigationToolbarButton(button, title) {
				if (!button) {
					return;
				}
				var isBlocked = isEditDirty();
				button.classList.toggle('disabled', isBlocked);
				button.setAttribute('aria-disabled', isBlocked ? 'true' : 'false');
				button.style.pointerEvents = isBlocked ? 'none' : '';
				button.style.opacity = isBlocked ? '0.5' : '';
				if (button.tagName === 'BUTTON') {
					button.disabled = isBlocked;
				}
				if (isBlocked) {
					button.setAttribute('tabindex', '-1');
					button.title = title;
				} else {
					button.removeAttribute('tabindex');
					button.title = '';
				}
			}

			function syncEditPrevNextToolbarButtons() {
				syncEditNavigationToolbarButton(getEditPrevToolbarButton(), Joomla.Text._('COM_BREEZINGFORMSNG_TEST_SAVE_PIECE_BEFORE_NAVIGATION'));
				syncEditNavigationToolbarButton(getEditNextToolbarButton(), Joomla.Text._('COM_BREEZINGFORMSNG_TEST_SAVE_PIECE_BEFORE_NAVIGATION'));
			}

			function syncEditUnitTestsButton() {
				var button = document.getElementById('bf-edit-piece-unit-tests-button');
				var field = document.getElementById('unit_tests');
				if (!button || !field) {
					return;
				}
				var currentValue = String(field.value || '');
				var hasTests = currentValue.trim() !== '';
				var isDirty = isEditTestBlocked();
				var enabled = hasTests && __bfOpts.hasSavedRecord && !isDirty;
				button.disabled = !enabled;
				button.classList.toggle('disabled', !enabled);
				button.setAttribute('aria-disabled', enabled ? 'false' : 'true');
				if (!__bfOpts.hasSavedRecord) {
					button.title = Joomla.Text._('COM_BREEZINGFORMSNG_TEST_SAVE_FIRST_PIECE');
				} else if (isDirty) {
					button.title = Joomla.Text._('COM_BREEZINGFORMSNG_TEST_SAVE_PIECE_BEFORE_UNIT_TESTS');
				} else {
					button.title = enabled ? '' : Joomla.Text._('COM_BREEZINGFORMSNG_TEST_UNIT_TESTS_NONE');
				}
			}

			function runPieceUnitTestsFromEdit() {
				var form = document.adminForm;
				if (!form) {
					return false;
				}
				var button = document.getElementById('bf-edit-piece-unit-tests-button');
				if (button && button.disabled) {
					return false;
				}
				var resultBox = document.getElementById('bf-edit-piece-unit-tests-status');
				var summary = document.getElementById('bf-edit-piece-unit-tests-summary');
				var detailsWrap = document.getElementById('bf-edit-piece-unit-tests-details-wrap');
				var details = document.getElementById('bf-edit-piece-unit-tests-details');
				var payload = new URLSearchParams();
				payload.set('option', form.option.value);
				payload.set('view', 'pieces');
				payload.set('task', 'pieces.runTestsAjax');
				payload.set('pkg', form.pkg.value || '');
				payload.set('id', form.id.value || '');
				payload.set('code', document.getElementById('code').value || '');
				payload.set('unit_tests', document.getElementById('unit_tests').value || '');
				payload.set('test_function', document.getElementById('name').value || '');
				payload.set(__bfOpts.csrfToken, '1');

				resultBox.style.display = 'block';
				resultBox.className = 'alert alert-info mt-3';
				summary.textContent = Joomla.Text._('COM_BREEZINGFORMSNG_TEST_RUNNING');
				detailsWrap.style.display = 'none';
				details.textContent = '';

					fetch('index.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
						},
						body: payload.toString()
					})
						.then(function (response) { return response.text(); })
						.then(function (text) {
							var data;
							var normalizedText = String(text || '').trim();
							var jsonMarkers = ['{"error"', '{"warning"', '{"total"'];
							var jsonStart = -1;
							for (var i = 0; i < jsonMarkers.length; i++) {
								var markerIndex = normalizedText.indexOf(jsonMarkers[i]);
								if (markerIndex !== -1) {
									jsonStart = markerIndex;
									break;
								}
							}
							var jsonEnd = normalizedText.lastIndexOf('}');
							if (jsonStart !== -1 && jsonEnd !== -1 && jsonEnd >= jsonStart) {
								normalizedText = normalizedText.slice(jsonStart, jsonEnd + 1);
							}
							try {
								data = JSON.parse(normalizedText);
							} catch (e) {
								throw new Error(text || Joomla.Text._('COM_BREEZINGFORMSNG_TEST_INVALID_SERVER_RESPONSE'));
							}
							return data;
						})
						.then(function (data) {
							if (data.error) {
								resultBox.className = 'alert alert-danger mt-3';
							summary.textContent = data.error;
							return;
						}
						if (data.warning) {
							resultBox.className = 'alert alert-warning mt-3';
							summary.textContent = data.warning;
							return;
						}
						resultBox.className = (data.failures && data.failures.length) ? 'alert alert-warning mt-3' : 'alert alert-success mt-3';
						summary.textContent = String(data.passed || 0) + '/' + String(data.total || 0) + ' ' + Joomla.Text._('COM_BREEZINGFORMSNG_TEST_PASSED_SHORT');
						if (data.failures && data.failures.length) {
							detailsWrap.style.display = 'block';
							details.textContent = data.failures.join('\n\n');
						}
					})
					.catch(function (error) {
						resultBox.className = 'alert alert-danger mt-3';
						summary.textContent = error && error.message ? error.message : String(error);
					});
				return false;
			}

			window.addEventListener('load', function () {
				var form = document.getElementById('adminForm');
				syncEditUnitTestsButton();
				syncEditSaveToolbarButton();
				syncEditTestToolbarButton();
				syncEditPrevNextToolbarButtons();
				if (form) {
					['input', 'change'].forEach(function (eventName) {
						form.addEventListener(eventName, function () {
							syncEditUnitTestsButton();
							syncEditSaveToolbarButton();
							syncEditTestToolbarButton();
							syncEditPrevNextToolbarButtons();
						});
					});
					window.setInterval(function () {
						syncEditUnitTestsButton();
						syncEditSaveToolbarButton();
						syncEditTestToolbarButton();
						syncEditPrevNextToolbarButtons();
					}, 500);
				}
			});
