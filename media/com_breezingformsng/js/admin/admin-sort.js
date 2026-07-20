function initialiseAdminSort() {
    document.querySelectorAll('.js-stools-column-order').forEach(function (link) {
        if (link.dataset.bfSortInitialised === 'true') {
            return;
        }

        link.dataset.bfSortInitialised = 'true';
        link.addEventListener('click', function (event) {
            event.preventDefault();

            var form = link.closest('form');
            var order = link.dataset.order || '';
            var direction = (link.dataset.direction || 'ASC').toLowerCase();

            if (!form || order === '') {
                return;
            }

            var setValue = function (name, value) {
                if (form.elements[name]) {
                    form.elements[name].value = value;
                }
            };

            setValue('filter_order', order);
            setValue('filter_order_Dir', direction);
            setValue('list[ordering]', order);
            setValue('list[direction]', direction);
            setValue('list[fullordering]', order + ' ' + direction.toUpperCase());
            setValue('limitstart', '0');
            setValue('list[start]', '0');

            form.submit();
        }, true);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiseAdminSort);
} else {
    initialiseAdminSort();
}
