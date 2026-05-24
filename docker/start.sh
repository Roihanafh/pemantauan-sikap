#!/usr/bin/env bash
set -e

: "${PORT:=8080}"

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan storage:link || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

echo "=== ENABLED MPM ==="
ls /etc/apache2/mods-enabled | grep mpm || true

exec apache2-foreground