#!/bin/bash
PORT=${PORT:-8080}
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

if [ ! -f /var/www/html/core/.env ]; then
  cp /var/www/html/core/.env.example /var/www/html/core/.env 2>/dev/null || touch /var/www/html/core/.env
fi

if [ -f /var/www/html/database.sql ] && [ -n "$DB_HOST" ]; then
  echo "Checking MySQL database tables..."
  TABLE_COUNT=$(mysql -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -N -e "SHOW TABLES;" 2>/dev/null | wc -l)
  if [ "$TABLE_COUNT" -eq "0" ]; then
    echo "Empty database detected! Importing database.sql..."
    mysql -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < /var/www/html/database.sql
    echo "Database imported successfully!"
  fi
fi

chown -R www-data:www-data /var/www/html/core/storage /var/www/html/core/bootstrap/cache
chmod -R 775 /var/www/html/core/storage /var/www/html/core/bootstrap/cache

apache2-foreground
