(function () {
    'use strict';

    if (window.BFJoomlaJQuery) {
        window.jQuery = window.BFJoomlaJQuery;
        window.$ = window.BFJoomlaDollar || window.BFJoomlaJQuery;
    }
}());
