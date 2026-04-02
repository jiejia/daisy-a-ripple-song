#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

BUILD_DIR="${ROOT_DIR}/build"
RUNTIME_DIR="${BUILD_DIR}/runtime"
INPUT_VENDOR_DIR="${RUNTIME_DIR}/vendor"
SCOPE_DIR="${BUILD_DIR}/scoped"

rm -rf "${RUNTIME_DIR}" "${SCOPE_DIR}"
mkdir -p "${RUNTIME_DIR}"

cp "${ROOT_DIR}/composer.json" "${RUNTIME_DIR}/composer.json"
if [[ -f "${ROOT_DIR}/composer.lock" ]]; then
  cp "${ROOT_DIR}/composer.lock" "${RUNTIME_DIR}/composer.lock"
fi

(cd "${RUNTIME_DIR}" && composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader)

ARS_SCOPER_INPUT_DIR="${INPUT_VENDOR_DIR}" \
  php -d error_reporting=6143 "${ROOT_DIR}/vendor/bin/php-scoper" add-prefix \
  --config="${ROOT_DIR}/scoper.inc.php" \
  --output-dir="${SCOPE_DIR}" \
  -f

echo "Scoped vendor built at: ${SCOPE_DIR}"
