function bfPiecesSyncPackage(form)
								{
									if (!form) {
										return;
									}
									if (form.pkgsel && form.pkg) {
										form.pkg.value = form.pkgsel.value === '' ? '- blank -' : form.pkgsel.value;
									}
								}

								function bfPiecesSubmitList(resetLimitStart)
								{
									var form = document.adminForm;
									if (!form) {
										return false;
									}
									if (resetLimitStart && form.limitstart) {
										form.limitstart.value = 0;
									}
									bfPiecesSyncPackage(form);
									Joomla.submitform('', form);
									return false;
								}
								window.bfPiecesSubmitList = bfPiecesSubmitList;


								function submitbutton(pressbutton)
								{
									var form = document.adminForm;
									var action = pressbutton.indexOf('.') === -1 ? pressbutton : pressbutton.split('.').pop();
									switch (action) {
										case 'copy':
										case 'publish':
										case 'unpublish':
										case 'remove':
											if (form.boxchecked.value==0) {
												alert(Joomla.Text._('COM_BREEZINGFORMSNG_PIECES_SELPIECESFIRST'));
												return;
											} // if
											break;
										default:
											break;
									} // switch
									if (action == 'remove') {
										if (!confirm(Joomla.Text._('COM_BREEZINGFORMSNG_PIECES_ASKDELETE'))) {
											return;
										}
									}
									bfPiecesSyncPackage(form);
									Joomla.submitform('pieces.' + action, form);
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
