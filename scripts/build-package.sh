#!/usr/bin/env bash

set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
output_dir="${1:-${root_dir}/build}"

if [[ "${output_dir}" != /* ]]; then
    output_dir="${root_dir}/${output_dir}"
fi

package_dir="${output_dir}/package"
manifest="${root_dir}/com_breezingformsng.xml"
version="$(sed -n 's:.*<version>\([^<]*\)</version>.*:\1:p' "${manifest}" | head -n 1)"

if [[ -z "${version}" ]]; then
    echo "Unable to resolve the component version." >&2
    exit 1
fi

rm -rf "${package_dir}"
mkdir -p "${package_dir}" "${output_dir}"

while IFS= read -r -d '' path; do
    mkdir -p "${package_dir}/$(dirname "${path}")"
    cp "${root_dir}/${path}" "${package_dir}/${path}"
done < <(
    git -C "${root_dir}" ls-files -z \
        administrator \
        components \
        media \
        com_breezingformsng.xml \
        script.php
)

# Install PHP dependencies (managed by Composer) into the package
composer install --no-dev --no-interaction --quiet \
    --working-dir="${package_dir}/administrator/components/com_breezingformsng"

# Prune TCPDF font families the component does not use: it relies on the
# helvetica core fonts plus a runtime TTF conversion of media/.../verdana.ttf
fonts_dir="${package_dir}/administrator/components/com_breezingformsng/vendor/tecnickcom/tcpdf/fonts"
if [[ -d "${fonts_dir}" ]]; then
    find "${fonts_dir}" -maxdepth 1 -type f \
        ! -name 'helvetica*' \
        ! -name 'courier*' \
        ! -name 'times*' \
        ! -name 'symbol*' \
        ! -name 'zapfdingbats*' \
        -delete
fi

archive="${output_dir}/com_breezingformsng-${version}.zip"
rm -f "${archive}"

(
    cd "${package_dir}"
    zip -qr "${archive}" .
)

printf '%s\n' "${archive}"
