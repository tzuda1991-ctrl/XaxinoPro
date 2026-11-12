#!/bin/bash
set -e

echo "=== Starting Xaxino on Railway ==="

PORT=${PORT:-8080}
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Ensure storage directories exist at runtime
mkdir -p /var/www/html/core/storage/app/public \
         /var/www/html/core/storage/framework/cache/data \
         /var/www/html/core/storage/framework/sessions \
         /var/www/html/core/storage/framework/views \
         /var/www/html/core/storage/logs \
         /var/www/html/core/bootstrap/cache

# Run PHP environment and database bootstrap
if [ -f /var/www/html/init_env.php ]; then
  php /var/www/html/init_env.php
fi

ENV_FILE="/var/www/html/core/.env"

# Ensure APP_KEY exists
if ! grep -q "^APP_KEY=base64:" "$ENV_FILE"; then
  echo "Generating Laravel APP_KEY..."
  php /var/www/html/core/artisan key:generate --force
fi

# Clean up database.sql for security (prevent public download)
rm -f /var/www/html/database.sql

# Optimize/cache clear
php /var/www/html/core/artisan optimize:clear || true

# Storage permissions
chown -R www-data:www-data /var/www/html/core/storage /var/www/html/core/bootstrap/cache
chmod -R 775 /var/www/html/core/storage /var/www/html/core/bootstrap/cache

echo "=== Launching Apache on port $PORT ==="
exec apache2-foreground
