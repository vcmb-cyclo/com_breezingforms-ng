var bf_htmltextareas = [];
var bf_htmltextareanames = [];
var bfHtmlTextareaRegistrations = [];

function bfRegisterHtmlTextarea(fieldName, valueProvider) {
    bfHtmlTextareaRegistrations.push({
        fieldName: fieldName,
        valueProvider: valueProvider
    });
}

function bf_htmltextareainit() {
    bfHtmlTextareaRegistrations.forEach(function (registration) {
        var field = JQuery('[name="' + registration.fieldName + '"]');

        field.val(JQuery.trim(field.val()) + ' ');
        bf_htmltextareas.push(registration.valueProvider());
        bf_htmltextareanames.push(registration.fieldName);
    });
}
