/* Extracted from BFQuickMode's inline process() script (Phase 9c step 2b,
   bfCalendarResponsive case) - the pickadate() initializer bound to each
   responsive calendar field. Only the per-field config (dbId, pickadate
   format/selectYears/firstDay, and whether the year-scroller buttons are
   available on this theme) is dynamic; everything else was byte-identical
   across ClassicRenderer/BootstrapRenderer/OnePageRenderer. Called once per
   field right after it is rendered. */
function bfInitCalendarResponsive(dbId, options) {
	JQuery(document).ready(function () {
		JQuery('body').append('<div class="bfCalendarResponsiveContainer' + dbId + '" style="display:block;position:absolute;left:-9999px;"></div>');
		JQuery('#ff_elem' + dbId + '_calendarButton').on('mousedown', function (event) {
			event.preventDefault();
		});
		JQuery('#ff_elem' + dbId + '_calendarButton').pickadate({
			format: options.format,
			selectYears: options.selectYears,
			selectMonths: true,
			editable: true,
			firstDay: options.firstDay,
			container: '.bfCalendarResponsiveContainer' + dbId,
			onClose: function () {
				JQuery('#ff_elem' + dbId + '_calendarButton').blur();
			},
			onOpen: function () {
				if (options.hasYearScroller) {
					bf_add_yearscroller(dbId);
				}
			},
			onSet: function () {
				JQuery('#ff_elem' + dbId).val(this.get('value'));
			}
		});
	});
}
