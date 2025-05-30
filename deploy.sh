#!/usr/bin/env bash
set -euo pipefail

echo "[1/5] Git pull"
git pull origin main

echo "[2/5] Composer install"
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "[3/5] Migrate"
php artisan migrate --force

echo "[4/5] Config cache"
php artisan config:cache

echo "[5/5] Route cache"
php artisan route:cache

echo "✅ Deploy finished"
