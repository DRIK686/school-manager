#!/bin/bash
# Deploy a NEW school from the template. Run as the school's own hosting user (not root),
# after the CWP account exists, the domain points at the server, and an empty MySQL database + user exist.
# Usage: bash deploy-new-school.sh [version-tag]      (default: latest tag)
set -euo pipefail

REPO_SSH="git@github.com:DRIK686/school-manager.git"
APP="$HOME/app"

[ "$(id -u)" -ne 0 ] || { echo "Run this as the school's hosting user, not root."; exit 1; }
[ ! -e "$APP" ] || { echo "$APP already exists. This script is for first-time installs only."; exit 1; }
command -v composer >/dev/null || { echo "composer not found for this user."; exit 1; }
echo "PHP version: $(php -r 'echo PHP_VERSION;')"
php -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);' || { echo "PHP 8.2+ is required. Pick it in CWP's PHP Selector."; exit 1; }

read -r -p "Site URL (e.g. https://school.example.com): " URL
case "$URL" in https://*|http://*) ;; *) echo "Include the https:// part."; exit 1;; esac
read -r -p "Database name: " DBN
read -r -p "Database user: " DBU
read -r -s -p "Database password: " DBP; echo
case "$DBP" in *"'"*) echo "Password contains a single quote; set a different one."; exit 1;; esac

# ---- GitHub access (read-only deploy key, one per school account) ----
mkdir -p ~/.ssh && chmod 700 ~/.ssh
[ -f ~/.ssh/id_ed25519 ] || ssh-keygen -q -t ed25519 -N "" -C "deploy-$(whoami)@$(hostname)" -f ~/.ssh/id_ed25519
ssh-keyscan -t ed25519,rsa github.com >> ~/.ssh/known_hosts 2>/dev/null; chmod 600 ~/.ssh/known_hosts
gh_ok() { local o; o=$(ssh -o BatchMode=yes -T git@github.com 2>&1 || true); [[ "$o" == *"successfully authenticated"* ]]; }
if ! gh_ok; then
  echo
  echo "Add this as a READ-ONLY deploy key: GitHub > DRIK686/school-manager > Settings > Deploy keys > Add"
  echo
  cat ~/.ssh/id_ed25519.pub
  echo
  read -r -p "Press Enter once it is added... " _
  gh_ok || { echo "GitHub still refuses the key."; exit 1; }
fi

# ---- Code ----
git clone -q "$REPO_SSH" "$APP"
cd "$APP"
TAG="${1:-$(git tag --sort=-v:refname | head -1)}"
git checkout -q "$TAG"
echo "Deploying version: $TAG"

# ---- Environment ----
cp .env.example .env
export URL DBN DBU DBP
python3 - <<'PY'
import os, re
keys = {
 'APP_ENV': 'production', 'APP_DEBUG': 'false', 'APP_URL': os.environ['URL'],
 'DB_CONNECTION': 'mysql', 'DB_HOST': '127.0.0.1', 'DB_PORT': '3306',
 'DB_DATABASE': os.environ['DBN'], 'DB_USERNAME': os.environ['DBU'],
 'DB_PASSWORD': "'" + os.environ['DBP'] + "'",
 'SESSION_DRIVER': 'file', 'CACHE_STORE': 'file', 'QUEUE_CONNECTION': 'sync', 'MAIL_MAILER': 'log',
}
lines = open('.env').read().splitlines()
pat = re.compile(r'^#?\s*(%s)=' % '|'.join(keys))
out = [l for l in lines if not pat.match(l)]
out += ['%s=%s' % kv for kv in keys.items()]
open('.env', 'w').write('\n'.join(out) + '\n')
PY
chmod 600 .env

mkdir -p bootstrap/cache storage/app/public storage/logs storage/framework/{sessions,views,cache/data}
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist -q
php artisan key:generate --force

# ---- Safety: right database, and empty ----
if ! ACTUAL=$(php artisan tinker --execute='echo DB::connection()->getDatabaseName();' 2>&1); then
  echo "Cannot connect to the database. Check the name, user and password."; echo "$ACTUAL"; exit 1
fi
[ "$ACTUAL" = "$DBN" ] || { echo "ABORT: connected to '$ACTUAL', expected '$DBN'."; exit 1; }
TABLES=$(php artisan tinker --execute='echo count(DB::select("SHOW TABLES"));')
[ "$TABLES" = "0" ] || { echo "ABORT: database '$DBN' is not empty ($TABLES tables). Use a new empty database."; exit 1; }

# ---- Install ----
php artisan migrate --force
php artisan school:install
php artisan storage:link
php artisan optimize:clear >/dev/null

# ---- Web root: public_html -> app/public ----
if [ -L "$HOME/public_html" ]; then rm "$HOME/public_html"
elif [ -d "$HOME/public_html" ]; then mv "$HOME/public_html" "$HOME/public_html.orig"; fi
ln -s "$APP/public" "$HOME/public_html"

# ---- Permissions (suPHP rejects group-writable PHP files) ----
find "$APP" -type d -exec chmod 755 {} +
find "$APP" -type f -name '*.php' -exec chmod 644 {} +

echo
echo "=============================================="
echo " Installed $TAG at $URL"
echo "=============================================="
echo " 1. Visit $URL and log in as the Super Admin."
echo " 2. Settings: upload the logo, set colours, currency and mail."
echo " 3. Website CMS: replace the placeholder text."
echo " 4. Finance > Accounts: enter real opening balances."
echo " 5. If the site is not on HTTPS yet, run AutoSSL in CWP now (the web root is set up)."
if grep -qE 'Schedule::|->schedule\(' routes/console.php app/Console/Kernel.php 2>/dev/null; then
  echo " 6. This version uses scheduled jobs. Add this cron entry for this user:"
  echo "      * * * * * cd $APP && php artisan schedule:run >> /dev/null 2>&1"
fi
echo
echo " Update later:  cd ~/app && git fetch --tags && git checkout <new-tag> && composer install --no-dev -o && php artisan migrate --force && php artisan optimize:clear"
echo " (Back up the database first. See UPDATING.md.)"
