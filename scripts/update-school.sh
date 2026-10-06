#!/usr/bin/env bash
# Update one school to a released version.
# Usage:  bash ~/app/scripts/update-school.sh v1.2.1 [app_dir]     (app_dir defaults to ~/app)
set -euo pipefail
TAG="${1:?usage: update-school.sh vX.Y.Z [app_dir]}"
APP="${2:-$HOME/app}"
cd "$APP"

if ! git diff --quiet || ! git diff --cached --quiet; then
  echo "Tracked files in $APP have local changes - commit or discard them first:"; git status --short | head
  exit 1
fi

echo "== fetching =="
git fetch --tags
git rev-parse -q --verify "refs/tags/$TAG" >/dev/null || { echo "Tag $TAG not found."; exit 1; }

echo "== switching to $TAG =="
git checkout "$TAG"
composer install --no-dev -o --no-interaction
php artisan migrate --force
php artisan optimize:clear

echo "== normalising permissions (suPHP) =="
find . \( -path ./vendor -o -path ./node_modules \) -prune -o -type f -name '*.php' -perm /022 -exec chmod 644 {} +
find . \( -path ./vendor -o -path ./storage -o -path ./bootstrap/cache -o -path ./node_modules \) -prune -o -type d -perm /022 -exec chmod 755 {} +
chmod 644 bootstrap/cache/*.php 2>/dev/null || true

echo "== done: now on $(git describe --tags) =="
