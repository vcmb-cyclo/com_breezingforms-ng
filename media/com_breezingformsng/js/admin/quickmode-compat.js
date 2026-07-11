/* Extracted from quickmode-app.js — jQuery back-compat shims and String helpers. */
(function () {
    'use strict';

if (window.JQuery && window.JQuery.fn && !window.JQuery.fn.on && window.JQuery.fn.bind) {
    window.JQuery.fn.on = function (types, selector, data, fn) {
        if (typeof selector === 'function') {
            return this.bind(types, selector);
        }

        if (typeof data === 'function') {
            return this.bind(types, data);
        }

        if (typeof fn === 'function') {
            return this.bind(types, fn);
        }

        return this.bind(types);
    };
}

if (window.JQuery && window.JQuery.fn && !window.JQuery.fn.prop) {
    window.JQuery.fn.prop = function (name, value) {
        if (arguments.length === 1) {
            return this.length ? this[0][name] : undefined;
        }

        return this.each(function () {
            this[name] = value;
        });
    };
}

if (window.JQuery && window.JQuery.fn && !window.JQuery.fn.off && window.JQuery.fn.unbind) {
    window.JQuery.fn.off = function (types, fn) {
        return this.unbind(types, fn);
    };
}

if (window.JQuery && window.JQuery.fn && !window.JQuery.fn.first) {
    window.JQuery.fn.first = function () {
        return this.eq(0);
    };
}

if (window.JQuery && !window.JQuery.isEmptyObject) {
    window.JQuery.isEmptyObject = function (object) {
        for (var name in object) {
            if (Object.prototype.hasOwnProperty.call(object, name)) {
                return false;
            }
        }

        return true;
    };
}

String.prototype.bfendsWith = function (suffix) {
    return this.match(suffix + "$") == suffix;
};

}());
