/* Extracted from quickmode-app.js — jQuery back-compat shims and String helpers. */
(function () {
    'use strict';

if (window.jQuery && window.jQuery.fn && !window.jQuery.fn.on && window.jQuery.fn.bind) {
    window.jQuery.fn.on = function (types, selector, data, fn) {
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

if (window.jQuery && window.jQuery.fn && !window.jQuery.fn.prop) {
    window.jQuery.fn.prop = function (name, value) {
        if (arguments.length === 1) {
            return this.length ? this[0][name] : undefined;
        }

        return this.each(function () {
            this[name] = value;
        });
    };
}

if (window.jQuery && window.jQuery.fn && !window.jQuery.fn.off && window.jQuery.fn.unbind) {
    window.jQuery.fn.off = function (types, fn) {
        return this.unbind(types, fn);
    };
}

if (window.jQuery && window.jQuery.fn && !window.jQuery.fn.first) {
    window.jQuery.fn.first = function () {
        return this.eq(0);
    };
}

if (window.jQuery && !window.jQuery.isEmptyObject) {
    window.jQuery.isEmptyObject = function (object) {
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
