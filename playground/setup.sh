#!/usr/bin/env bash
# Creates a basic local Statamic site in this folder with the statamic-emojis addon linked.
#
# Usage: ./setup.sh [--local | --ddev] [--no-seed]
#   --local    use the PHP and Composer installed on this machine
#   --ddev     run everything inside a DDEV project (https://ddev.com)
#   --no-seed  skip copying the test content from seed/ (blank Statamic site)
# Without --local/--ddev you are asked interactively (non-interactive default: --local).
set -euo pipefail
cd "$(dirname "$0")"

PACKAGE="jeslage/statamic-emojis"
DDEV_PROJECT="statamic-emojis"
DDEV_URL="https://${DDEV_PROJECT}.ddev.site"
# Name of the addon's folder (the parent of this playground), used for the DDEV mount.
ADDON_DIR="$(basename "$(cd .. && pwd)")"

mode=""
seed=1
for arg in "$@"; do
  case "$arg" in
    --local) mode="local" ;;
    --ddev) mode="ddev" ;;
    --no-seed) seed=0 ;;
    -h|--help)
      sed -n '2,8p' "$(basename "$0")" | sed 's/^# \{0,1\}//'
      exit 0
      ;;
    *)
      echo "Unknown option: $arg (use --local, --ddev or --no-seed)" >&2
      exit 1
      ;;
  esac
done

if [ -f artisan ]; then
  echo "Statamic is already installed here." >&2
  exit 0
fi

if [ -z "$mode" ]; then
  if [ -t 0 ]; then
    read -r -p "Set up with [1] local PHP or [2] DDEV? [1] " choice
    case "${choice:-1}" in
      1|local) mode="local" ;;
      2|ddev) mode="ddev" ;;
      *) echo "Invalid choice: $choice" >&2; exit 1 ;;
    esac
  else
    mode="local"
  fi
fi

require() {
  for cmd in "$@"; do
    if ! command -v "$cmd" >/dev/null 2>&1; then
      echo "'$cmd' is required for the $mode setup but was not found." >&2
      exit 1
    fi
  done
}

if [ "$mode" = "ddev" ]; then
  require ddev
  run_composer() { ddev composer "$@"; }
  # `ddev composer create-project` refuses subdirectories, so bypass the wrapper.
  run_create_project() { ddev exec composer create-project "$@"; }
  run_php() { ddev exec php "$@"; }
  run_bash() { ddev exec bash "$@"; }
  # With Mutagen (default on macOS) host and container are synced asynchronously.
  # Flush after host-side writes the container needs and vice versa. No-op otherwise.
  sync_files() { ddev mutagen sync >/dev/null 2>&1 || true; }
else
  require php composer
  run_composer() { composer "$@"; }
  run_create_project() { composer create-project "$@"; }
  run_php() { php "$@"; }
  run_bash() { bash "$@"; }
  sync_files() { :; }
fi

echo "Setting up the Statamic playground ($mode)..."

if [ "$mode" = "ddev" ]; then
  if [ ! -f .ddev/config.yaml ]; then
    ddev config \
      --project-name="$DDEV_PROJECT" \
      --project-type=laravel \
      --docroot=public \
      --create-docroot \
      --php-version=8.4
  fi
  # The addon is this folder's parent, outside the DDEV project root. Mount it at
  # /var/<addon-dir>: the container's project root is /var/www/html, so the Composer path
  # repository ../../<addon-dir> resolves on the host and in the container alike.
  cat > .ddev/docker-compose.addon.yaml <<YAML
services:
  web:
    volumes:
      - \${DDEV_APPROOT}/..:/var/${ADDON_DIR}
YAML
  ddev start
fi

# Install into a temp dir inside this folder (visible to the DDEV container), then move
# the files next to README.md / setup.sh without overwriting them. The move runs where
# Composer ran (container or host), so Mutagen doesn't have to shuffle vendor/ around.
tmp=".install"
rm -rf "$tmp"
trap 'rm -rf "$tmp"' EXIT
mkdir -p "$tmp"
cat > "$tmp/move.sh" <<'SH'
set -euo pipefail
shopt -s dotglob
for f in .install/site/*; do
  name="$(basename "$f")"
  case "$name" in README.md|setup.sh|seed|.ddev|.gitignore) continue ;; esac
  rm -rf "./$name"
  mv "$f" .
done
rm -rf .install/site
SH
sync_files
run_create_project statamic/statamic "$tmp/site" --prefer-dist --no-interaction
run_bash "$tmp/move.sh"
sync_files
rm -rf "$tmp"

if [ ! -f artisan ]; then
  echo "Statamic install failed: no artisan file in $(pwd)." >&2
  exit 1
fi

# Installing the addon runs vendor:publish, which needs the addon's Vite build directory.
# It only exists after `npm run build`, so create it (empty, gitignored) to avoid an error.
mkdir -p ../resources/dist/build
sync_files

# Link the addon via a path repository (symlinked, so edits apply immediately).
run_composer config repositories.statamic-emojis path "../../${ADDON_DIR}"
run_composer config minimum-stability dev
run_composer config prefer-stable true
run_composer require "${PACKAGE}:@dev" --no-interaction

# Asset container + blueprint for custom emojis (idempotent; the seed also ships them)
run_php please emojis:custom-setup

if [ "$mode" = "ddev" ]; then
  if grep -q "^APP_URL=" .env; then
    sed -i.bak "s|^APP_URL=.*|APP_URL=${DDEV_URL}|" .env && rm -f .env.bak
  else
    printf 'APP_URL=%s\n' "$DDEV_URL" >> .env
  fi
  sync_files
fi

if [ "$seed" = 1 ]; then
  # Pages collection with a blueprint covering the addon's fields, plus test entries.
  echo "Copying test content from seed/..."
  cp -R seed/. .
  sync_files
  run_php please stache:refresh
fi

run_php please make:user --super || true

echo
if [ "$mode" = "ddev" ]; then
  echo "Done. Open ${DDEV_URL}/cp (ddev launch /cp)."
else
  echo "Done. Run: php artisan serve, then open http://127.0.0.1:8000/cp"
fi
echo "Addon assets: run 'npm run dev' in the addon root (see README.md)."
