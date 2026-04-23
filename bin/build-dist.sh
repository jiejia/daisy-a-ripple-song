#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

VERSION="${1:-}"
if [[ -z "${VERSION}" ]]; then
  if command -v git >/dev/null 2>&1 && git -C "${ROOT_DIR}" rev-parse --git-dir >/dev/null 2>&1; then
    VERSION="$(git -C "${ROOT_DIR}" describe --tags --always --dirty)"
  else
    VERSION="dev"
  fi
fi

BUILD_DIR="${ROOT_DIR}/build"
SCOPE_DIR="${BUILD_DIR}/scoped"
DIST_ROOT="${BUILD_DIR}/dist"
DIST_THEME_DIR="${DIST_ROOT}/daisy-a-ripple-song"

rm -rf "${DIST_ROOT}"
mkdir -p "${DIST_THEME_DIR}"

if [[ ! -f "${SCOPE_DIR}/autoload.php" ]]; then
  echo "Missing scoped vendor in ${SCOPE_DIR}. Run: composer run scoper:build"
  exit 1
fi

if [[ ! -f "${ROOT_DIR}/public/dist/.vite/manifest.json" ]]; then
  echo "Missing built frontend assets in public/dist. Run: npm run build"
  exit 1
fi

rsync -a --delete \
  --exclude ".DS_Store" \
  --exclude "/.git/" \
  --exclude "/.github/" \
  --exclude "/.gitignore" \
  --exclude "/.idea/" \
  --exclude "/.phpunit.cache/" \
  --exclude "/src/Tests/" \
  --exclude "/bin/" \
  --exclude "/build/" \
  --exclude "/composer.json" \
  --exclude "/composer.lock" \
  --exclude "/node_modules/" \
  --exclude "/package.json" \
  --exclude "/package-lock.json" \
  --exclude "/phpunit.xml" \
  --exclude "/scoper.inc.php" \
  --exclude "/vendor/" \
  --exclude "vite.config.js" \
  "${ROOT_DIR}/" "${DIST_THEME_DIR}/"

rsync -a --delete "${SCOPE_DIR}/" "${DIST_THEME_DIR}/vendor/"

cat > "${DIST_THEME_DIR}/vendor/scoper-autoload.php" <<'PHP'
<?php
/**
 * Autoload bridge for PHP-Scoper prefixed vendor.
 */

$loader = require __DIR__ . '/autoload.php';

$prefix = 'ARippleSong\\Themes\\Daisy\\Vendor\\';
$prefixLength = strlen($prefix);

spl_autoload_register(
    static function ($class) use ($loader, $prefix, $prefixLength) {
        if (strncmp($class, $prefix, $prefixLength) !== 0) {
            return;
        }

        $unprefixed = substr($class, $prefixLength);
        if ($unprefixed === '') {
            return;
        }

        $loader->loadClass($unprefixed);
    },
    true,
    true
);

return $loader;
PHP

ZIP_NAME="${ARS_DIST_ZIP_NAME:-${2:-}}"
if [[ -z "${ZIP_NAME}" ]]; then
  ZIP_NAME="daisy-a-ripple-song-${VERSION}.zip"
fi

ZIP_PATH="${BUILD_DIR}/${ZIP_NAME}"
rm -f "${ZIP_PATH}"

(cd "${DIST_ROOT}" && zip -qr "${ZIP_PATH}" "daisy-a-ripple-song")

echo "Built: ${ZIP_PATH}"
