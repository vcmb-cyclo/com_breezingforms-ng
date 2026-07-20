/* Bootstrap 5 tooltip initialization shared by Bootstrap and OnePage themes. */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.hasTooltip').forEach(function (element) {
        bootstrap.Tooltip.getOrCreateInstance(element, {
            container: 'body',
            html: true
        });
    });
});
