(function () {
    'use strict';

    document.addEventListener('change', function (event) {
        var target = event.target;

        if (!(target instanceof HTMLInputElement)) {
            return;
        }

        var group = target.dataset.bfSelectAll || target.dataset.bfSelectItem || '';

        if (group === '') {
            return;
        }

        var masterSelector = '[data-bf-select-all="' + group + '"]';
        var itemSelector = '[data-bf-select-item="' + group + '"]';

        if (target.matches(masterSelector)) {
            document.querySelectorAll(itemSelector).forEach(function (checkbox) {
                checkbox.checked = target.checked;
            });
        }

        var masters = document.querySelectorAll(masterSelector);
        var items = document.querySelectorAll(itemSelector);
        var checkedCount = document.querySelectorAll(itemSelector + ':checked').length;

        masters.forEach(function (master) {
            master.checked = items.length > 0 && checkedCount === items.length;
            master.indeterminate = checkedCount > 0 && checkedCount < items.length;
        });
    });
}());
