#!/usr/bin/env sh
set -e
mkdir -p /var/www/html/storage/logs /var/www/html/bootstrap/cache
chmod -R ug+rwX /var/www/html/storage /var/www/html/bootstrap/cache || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
if [ -f /var/www/html/artisan ]; then
  php /var/www/html/artisan optimize:clear >/dev/null 2>&1 || true
fi
exec "$@"
