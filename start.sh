#!/bin/bash
set -e

echo "=== Starting Xaxino on Railway ==="

PORT=${PORT:-8080}
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

ENV_FILE="/var/www/html/core/.env"

# Copy default .env if not exists
if [ ! -f "$ENV_FILE" ]; then
  cp /var/www/html/core/.env.example "$ENV_FILE" 2>/dev/null || touch "$ENV_FILE"
fi

# Parse MYSQL_URL or DATABASE_URL if individual vars not provided
if [ -z "$DB_HOST" ] && [ -n "$MYSQL_URL" ]; then
  echo "Parsing MYSQL_URL..."
  DB_URL_CLEAN="${MYSQL_URL#*://}"
  DB_USERPASS="${DB_URL_CLEAN%%@*}"
  DB_HOSTPORTDB="${DB_URL_CLEAN#*@}"
  
  DB_USERNAME="${DB_USERPASS%%:*}"
  DB_PASSWORD="${DB_USERPASS#*:}"
  
  DB_HOSTPORT="${DB_HOSTPORTDB%%/*}"
  DB_HOST="${DB_HOSTPORT%%:*}"
  DB_PORT="${DB_HOSTPORT#*:}"
  [ "$DB_PORT" = "$DB_HOST" ] && DB_PORT=3306
  
  DB_DATABASE="${DB_HOSTPORTDB#*/}"
  DB_DATABASE="${DB_DATABASE%%\?*}"
fi

# Fallback to standard Railway MySQL variables
DB_HOST="${DB_HOST:-${MYSQLHOST:-127.0.0.1}}"
DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-railway}}"
DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-root}}"
DB_PASSWORD="${DB_PASSWORD:-$MYSQLPASSWORD}"

# Determine APP_URL
if [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
  APP_URL="https://$RAILWAY_PUBLIC_DOMAIN"
elif [ -n "$RAILWAY_STATIC_URL" ]; then
  APP_URL="https://$RAILWAY_STATIC_URL"
elif [ -z "$APP_URL" ]; then
  APP_URL="http://localhost:$PORT"
fi

echo "App URL: $APP_URL"
echo "Database Host: $DB_HOST:$DB_PORT (Database: $DB_DATABASE, User: $DB_USERNAME)"

# Function to update or append in .env
set_env() {
  local key="$1"
  local value="$2"
  if grep -q "^${key}=" "$ENV_FILE"; then
    sed -i "s|^${key}=.*|${key}=${value}|g" "$ENV_FILE"
  elif grep -q "^# ${key}=" "$ENV_FILE"; then
    sed -i "s|^# ${key}=.*|${key}=${value}|g" "$ENV_FILE"
  else
    echo "${key}=${value}" >> "$ENV_FILE"
  fi
}

set_env "APP_NAME" "Xaxino"
set_env "APP_ENV" "production"
set_env "APP_DEBUG" "false"
set_env "APP_URL" "$APP_URL"
set_env "DB_CONNECTION" "mysql"
set_env "DB_HOST" "$DB_HOST"
set_env "DB_PORT" "$DB_PORT"
set_env "DB_DATABASE" "$DB_DATABASE"
set_env "DB_USERNAME" "$DB_USERNAME"
set_env "DB_PASSWORD" "\"$DB_PASSWORD\""
set_env "SESSION_DRIVER" "file"
set_env "CACHE_STORE" "file"
set_env "QUEUE_CONNECTION" "sync"

# Wait for MySQL to become available if remote host is provided
if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ] && [ "$DB_HOST" != "localhost" ]; then
  echo "Waiting for MySQL at $DB_HOST:$DB_PORT to be ready..."
  for i in {1..30}; do
    if mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent 2>/dev/null; then
      echo "MySQL is reachable!"
      break
    fi
    echo "Waiting for MySQL connection... ($i/30)"
    sleep 2
  done

  # Check if tables exist
  TABLE_COUNT=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -N -e "SHOW TABLES;" 2>/dev/null | wc -l || echo "0")
  echo "Found $TABLE_COUNT tables in database $DB_DATABASE"
  
  if [ "$TABLE_COUNT" -eq "0" ] && [ -f /var/www/html/database.sql ]; then
    echo "Empty database detected! Importing database.sql..."
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < /var/www/html/database.sql
    echo "database.sql imported successfully!"
  fi
fi

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
