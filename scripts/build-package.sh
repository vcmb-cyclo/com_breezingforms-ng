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
        site \
        com_breezingformsng.xml \
        script.php
)

archive="${output_dir}/com_breezingformsng-${version}.zip"
rm -f "${archive}"

(
    cd "${package_dir}"
    zip -qr "${archive}" .
)

printf '%s\n' "${archive}"
