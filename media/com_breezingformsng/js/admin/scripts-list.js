function bfScriptsSyncPackage(form)
								{
									if (!form) {
										return;
									}
									if (form.pkgsel && form.pkg) {
										form.pkg.value = form.pkgsel.value === '' ? '- blank -' : form.pkgsel.value;
									}
								}

								function bfScriptsSubmitList(resetLimitStart)
								{
									var form = document.adminForm;
									if (!form) {
										return false;
									}
									if (resetLimitStart && form.limitstart) {
										form.limitstart.value = 0;
									}
									bfScriptsSyncPackage(form);
									Joomla.submitform('', form);
									return false;
								}

								function submitbutton(pressbutton)
								{
									var form = document.adminForm;
									switch (pressbutton) {
										case 'copy':
										case 'publish':
										case 'unpublish':
										case 'remove':
											if (form.boxchecked.value==0) {
												alert(Joomla.Text._('COM_BREEZINGFORMSNG_SCRIPTS_SELSCRIPTSFIRST'));
												return;
											} // if
											break;
										default:
											break;
									} // switch
									if (pressbutton == 'remove') {
										if (!confirm(Joomla.Text._('COM_BREEZINGFORMSNG_SCRIPTS_ASKDELETE'))) {
											return;
										}
									}
									bfScriptsSyncPackage(form);
									var task = pressbutton === 'new' ? 'add' : pressbutton;
									Joomla.submitform('scripts.' + task, form);
								} // submitbutton
								Joomla.submitbutton = submitbutton;

			function listItemTask(id, task) {
				var f = document.adminForm;
				var cb = document.getElementById(id);
				if (cb && f) {
					var checkboxes = f.querySelectorAll('input[type="checkbox"][id^="cb"]');
					for (var i = 0; i < checkboxes.length; i++) {
						checkboxes[i].checked = false;
					}
					cb.checked = true;
					f.boxchecked.value = 1;
					Joomla.submitbutton(task);
				}
				return false;
			} // listItemTask
			window.listItemTask = listItemTask;
