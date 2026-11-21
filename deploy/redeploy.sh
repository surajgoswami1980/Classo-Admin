#!/usr/bin/env bash
# Pull latest code, install, migrate, cache, and restart the Laravel admin.
# Usage (on the server):  bash deploy/redeploy.sh
set -euo pipefail

cd "$(dirname "$0")/.."   # repo/school-erp-admin

echo "→ Pulling latest code"
git pull

echo "→ Installing dependencies"
composer install --no-dev --optimize-autoloader

echo "→ Running migrations"
php artisan migrate --force

echo "→ Ensuring storage symlink"
php artisan storage:link || true

echo "→ Rebuilding caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "→ Restarting queue worker"
sudo supervisorctl restart quilo-queue:* || true

echo "✓ Admin deployed."
