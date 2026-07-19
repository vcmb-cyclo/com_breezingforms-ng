function bfRegisterDeactivatedSection(sectionName) {
    bfDeactivateSection.push(sectionName);
}

function bfRegisterDeactivatedField(fieldName) {
    bfDeactivateField['ff_nm_' + fieldName + '[]'] = true;
}

function bfRegisterNonMobileFileField(fieldName) {
    if (!navigator.userAgent.match(/(iPad|iPhone|iPod|Android)/i)) {
        bfRegisterDeactivatedField(fieldName);
    }
}
