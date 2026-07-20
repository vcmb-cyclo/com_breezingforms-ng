#!/usr/bin/env bash

set -euo pipefail

archive="${1:?Usage: scripts/joomla-install-smoke.sh path/to/package.zip}"
joomla_image="${JOOMLA_IMAGE:-joomla:6.0-apache}"
mysql_image="${MYSQL_IMAGE:-mysql:8.4}"
run_id="${GITHUB_RUN_ID:-local}-$$"
network="bfng-smoke-${run_id}"
db_container="bfng-smoke-db-${run_id}"
web_container="bfng-smoke-web-${run_id}"
container_archive="/tmp/com_breezingformsng.zip"

cleanup() {
    if [[ "${KEEP_SMOKE_CONTAINERS:-0}" == "1" ]]; then
        echo "Smoke containers kept: ${web_container}, ${db_container}; network: ${network}" >&2
        return
    fi

    docker rm -f "${web_container}" "${db_container}" >/dev/null 2>&1 || true
    docker network rm "${network}" >/dev/null 2>&1 || true
}
trap cleanup EXIT

docker network create "${network}" >/dev/null

docker run -d \
    --name "${db_container}" \
    --network "${network}" \
    -e MYSQL_DATABASE=joomla \
    -e MYSQL_USER=joomla \
    -e MYSQL_PASSWORD=joomla \
    -e MYSQL_ROOT_PASSWORD=root \
    "${mysql_image}" >/dev/null

for _ in $(seq 1 60); do
    if docker exec -e MYSQL_PWD=root "${db_container}" mysqladmin ping -uroot --silent >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

docker exec -e MYSQL_PWD=root "${db_container}" mysqladmin ping -uroot --silent >/dev/null

docker run -d \
    --name "${web_container}" \
    --network "${network}" \
    -e JOOMLA_DB_HOST="${db_container}" \
    -e JOOMLA_DB_USER=joomla \
    -e JOOMLA_DB_PASSWORD=joomla \
    -e JOOMLA_DB_NAME=joomla \
    -e JOOMLA_SITE_NAME="BreezingForms NG Smoke Test" \
    -e JOOMLA_ADMIN_USER="Smoke Administrator" \
    -e JOOMLA_ADMIN_USERNAME=smokeadmin \
    -e JOOMLA_ADMIN_PASSWORD='Smoke-Test-123!' \
    -e JOOMLA_ADMIN_EMAIL=smoke@example.invalid \
    -e JOOMLA_INSTALLATION_DISABLE_LOCALHOST_CHECK=1 \
    "${joomla_image}" >/dev/null

for _ in $(seq 1 90); do
    if docker exec "${web_container}" test -f /var/www/html/configuration.php; then
        break
    fi
    sleep 2
done

docker exec "${web_container}" test -f /var/www/html/configuration.php

for _ in $(seq 1 60); do
    if docker exec "${web_container}" php -r '
        exit(@file_get_contents("http://127.0.0.1/index.php") === false ? 1 : 0);
    '; then
        break
    fi
    sleep 2
done

docker exec "${web_container}" php -r '
    exit(@file_get_contents("http://127.0.0.1/index.php") === false ? 1 : 0);
'

docker cp "${archive}" "${web_container}:${container_archive}" >/dev/null
docker exec -e HTTP_HOST=localhost "${web_container}" php /var/www/html/cli/joomla.php extension:install \
    --path="${container_archive}" \
    --live-site=http://localhost \
    --quiet \
    --no-interaction

table_prefix="$(
    docker exec "${web_container}" php -r '
        require "/var/www/html/configuration.php";
        $config = new JConfig();
        echo $config->dbprefix;
    '
)"

component_count="$(
    docker exec -e MYSQL_PWD=joomla "${db_container}" mysql -N -ujoomla joomla \
        -e "SELECT COUNT(*) FROM \`${table_prefix}extensions\` WHERE type = 'component' AND element = 'com_breezingformsng';"
)"

if [[ "${component_count}" -ne 1 ]]; then
    echo "BreezingForms NG component registration was not found." >&2
    docker exec -e MYSQL_PWD=joomla "${db_container}" mysql -N -ujoomla joomla \
        -e "SELECT extension_id, type, element, name FROM \`${table_prefix}extensions\` WHERE element LIKE '%breezingform%' OR name LIKE '%BreezingForms%';" >&2
    exit 1
fi

plugin_count="$(
    docker exec -e MYSQL_PWD=joomla "${db_container}" mysql -N -ujoomla joomla \
        -e "SELECT COUNT(*) FROM \`${table_prefix}extensions\` WHERE type = 'plugin' AND element = 'bfcompat' AND folder = 'system';"
)"

if [[ "${plugin_count}" -ne 1 ]]; then
    echo "The BreezingForms NG compatibility plugin was not installed correctly." >&2
    exit 1
fi

# Component tables are prefixed facileforms_ (carried over from the
# original FacileForms/BreezingForms naming), not breezingformsng_.
table_count="$(
    docker exec -e MYSQL_PWD=joomla "${db_container}" mysql -N -ujoomla joomla \
        -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '${table_prefix}facileforms_%';"
)"

if [[ "${table_count}" -lt 14 ]]; then
    echo "BreezingForms NG tables were not installed correctly: ${table_count} found (expected >= 14)." >&2
    exit 1
fi

# Exercise the update path: installing the same package again over an
# existing install must succeed without errors and leave the same tables
# and registrations in place.
docker exec -e HTTP_HOST=localhost "${web_container}" php /var/www/html/cli/joomla.php extension:install \
    --path="${container_archive}" \
    --live-site=http://localhost \
    --quiet \
    --no-interaction

table_count_after_update="$(
    docker exec -e MYSQL_PWD=joomla "${db_container}" mysql -N -ujoomla joomla \
        -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '${table_prefix}facileforms_%';"
)"

if [[ "${table_count_after_update}" -ne "${table_count}" ]]; then
    echo "Table count changed after re-running the installer as an update: ${table_count} -> ${table_count_after_update}." >&2
    exit 1
fi

# Frontend sanity check: the site must still render after installation
# (catches a fatal error in the system plugin or a broken menu item).
frontend_status="$(
    docker exec "${web_container}" php -r '
        $context = stream_context_create(["http" => ["ignore_errors" => true]]);
        file_get_contents("http://127.0.0.1/index.php", false, $context);
        foreach ($http_response_header ?? [] as $header) {
            if (preg_match("#^HTTP/\\S+\\s+(\\d+)#", $header, $m)) {
                echo $m[1];
                break;
            }
        }
    '
)"

if [[ "${frontend_status}" != "200" ]]; then
    echo "Frontend did not respond with HTTP 200 after installation (got: ${frontend_status:-<empty>})." >&2
    exit 1
fi

# Generate an image with the bundled CAPTCHA runtime. This catches missing
# Securimage support files and PHP/GD incompatibilities after library updates.
docker exec "${web_container}" php -r '
    define("_JEXEC", 1);
    require "/var/www/html/administrator/components/com_breezingformsng/libraries/securimage/securimage.php";
    $captcha = new Securimage(["no_exit" => true, "send_headers" => false]);
    ob_start();
    $captcha->show();
    $image = ob_get_clean();
    exit(str_starts_with($image, "\x89PNG\r\n\x1a\n") ? 0 : 1);
'

echo "Joomla installation, update and frontend smoke tests passed."
