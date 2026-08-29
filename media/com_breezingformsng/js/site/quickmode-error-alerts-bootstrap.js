/* Extracted from BFQuickModeBootstrap's inline headers() script (Phase 9c
   step 2b) - the bfShowErrors() display function for the Bootstrap theme,
   only loaded when the form's useErrorAlerts property is disabled. Kept
   separate from the classic theme's quickmode-error-alerts.js because the
   Bootstrap variant doesn't have the bfSignature-specific validation
   prompt branch. Depends on two globals declared inline right before this
   file is loaded: bfShowDefaultErrors, bfErrorPageScoped and
   ff_processor.form_id.

   The per-field "balloon" error prompt branch that used to run here
   (JQuery(...).bfvalidationEngine(...), buildPrompt()/closePrompt())
   was removed: it called a method that was never actually defined
   anywhere in this codebase (the vendored plugin only exposes itself
   as $.fn.validationEngine, not $.fn.bfvalidationEngine), guarded by
   a check on a property that was equally never set - so the branch
   had been dead code, unreachable, since whenever this "bf" renaming
   happened. The vendored jquery.validationEngine.js/-en.js library
   and its CSS have been removed along with it (see
   docs/maintenance/js-libraries-migration-plan.md). */
				function bfShowErrors(error){
                                        if (bfShowDefaultErrors) {
                                            JQuery(".bfErrorMessage").html("");
                                            JQuery(".bfErrorMessage").css("display","none");
                                            if (bfErrorPageScoped) {
                                                JQuery("#bfPage" + ff_currentpage + " .bfErrorMessage").fadeIn(1500);
                                            } else {
                                                JQuery(".bfErrorMessage").fadeIn(1500);
                                            }
                                            var allErrors = "";
                                            var errors = error.split("\n");
                                            for(var i = 0; i < errors.length; i++){
                                                allErrors += "<div class=\"bfError\">" + errors[i] + "</div>";
                                            }
                                            JQuery(".bfErrorMessage").html(allErrors);
                                            if (!bfErrorPageScoped) {
                                                JQuery(".bfErrorMessage").css("display","");
                                            }
                                        }
				}
