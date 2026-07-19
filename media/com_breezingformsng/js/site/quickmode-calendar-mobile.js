function bfInitMobileCalendar(dbId, openLabel) {
    JQuery(document).ready(function () {
        setTimeout(function () {
            JQuery('.js-calendar').css('display', 'none');

            JQuery('#ff_elem' + dbId + '_btn').on('click', function () {
                JQuery(this).closest('.input-group').next('.js-calendar').css('display', 'block');
            });

            JQuery('.js-calendar .btn-exit, .js-calendar .btn-today, .js-calendar .day').on('click', function () {
                JQuery(this).closest('.js-calendar').css('display', 'none');
            });

            JQuery('#ff_elem' + dbId + '_btn').html(openLabel);
        }, 100);
    });
}
