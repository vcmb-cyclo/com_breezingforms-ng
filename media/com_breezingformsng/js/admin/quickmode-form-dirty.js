/**
 * BreezingForms NG - QuickMode "unsaved changes" badge (charte graphique).
 *
 * Scope: tracks both edits to the current page/section/element's fields
 * across the Proprietes/Avance/Options tabs (form#bfForm, including its
 * CodeMirror editors) and tree structure changes - create/move/delete of a
 * page/section/element - which quickmode-app.js makes directly against its
 * in-memory app.dataObject, outside of any form. Renaming a node happens
 * through the Proprietes tab's Titre/Nom fields (already covered by
 * bfForm), not a separate tree-inline-rename control, so it doesn't need
 * its own hook. window.BFQMApp is quickmode-app.js's own app instance,
 * exposed there specifically for this file to read app.dataObject.
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', function () {
    var form = document.forms.bfForm;
    var badge = document.getElementById('bfUnsavedBadge');

    if (!form || !badge) {
        return;
    }

    function editorValues() {
        var instances = (window.Joomla && Joomla.editors && Joomla.editors.instances) || {};

        return Object.keys(instances).sort().map(function (name) {
            try {
                return name + '=' + instances[name].getValue();
            } catch (e) {
                return name + '=';
            }
        }).join(' ');
    }

    function treeState() {
        try {
            return JSON.stringify(window.BFQMApp.dataObject);
        } catch (e) {
            return '';
        }
    }

    function formState() {
        return new URLSearchParams(new FormData(form)).toString() + ' ' + editorValues() + ' ' + treeState();
    }

    var initialState = formState();

    function sync() {
        badge.hidden = formState() === initialState;
    }

    form.addEventListener('input', sync);
    form.addEventListener('change', sync);

    // Neither CodeMirror (via Joomla.editors.instances) nor tree structure
    // edits (app.dataObject, mutated directly by quickmode-app.js's
    // create/move/delete handlers) fire native input/change events on
    // bfForm - poll instead, matching the 500ms interval already used
    // elsewhere in quickmode-app.js for editor visibility.
    setInterval(sync, 500);
});
