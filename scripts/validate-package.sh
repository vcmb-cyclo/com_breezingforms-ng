#!/usr/bin/env bash

set -euo pipefail

archive="${1:?Usage: scripts/validate-package.sh path/to/package.zip}"

if [[ ! -f "${archive}" ]]; then
    echo "Package not found: ${archive}" >&2
    exit 1
fi

entries="$(unzip -Z1 "${archive}")"

required=(
    "com_breezingformsng.xml"
    "script.php"
    "administrator/components/com_breezingformsng/services/provider.php"
    "administrator/components/com_breezingformsng/src/Extension/BreezingFormsNGComponent.php"
    "administrator/components/com_breezingformsng/sql/install.mysql.utf8.sql"
    "administrator/components/com_breezingformsng/plugins/sysbreezingforms/sysbreezingforms.xml"
    "administrator/components/com_breezingformsng/plugins/bfcompat/bfcompat.xml"
    "components/com_breezingformsng/breezingformsng.php"
    "media/com_breezingformsng/css/custom.css"
)

for path in "${required[@]}"; do
    if ! grep -Fxq "${path}" <<<"${entries}"; then
        echo "Required package entry is missing: ${path}" >&2
        exit 1
    fi
done

forbidden_patterns=(
    '^\.git/'
    '^\.github/'
    '^scripts/'
    '^build/'
    '/cache/'
    '/logs/'
)

for pattern in "${forbidden_patterns[@]}"; do
    if grep -Eq "${pattern}" <<<"${entries}"; then
        echo "Forbidden development artifact found in package: ${pattern}" >&2
        exit 1
    fi
done

if ! unzip -p "${archive}" com_breezingformsng.xml \
    | grep -Fq '<extension type="component" version="6.0" method="upgrade">'; then
    echo "Invalid Joomla component manifest." >&2
    exit 1
fi

echo "Package validation passed: ${archive}"
