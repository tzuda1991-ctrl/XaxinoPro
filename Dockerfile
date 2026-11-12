FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    libicu-dev libonig-dev libxml2-dev zip unzip curl default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip intl soap

# Enable Apache modules: rewrite, headers, env & ensure ONLY mpm_prefork is loaded
RUN a2enmod rewrite headers env \
    && rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork

# Apache VirtualHost & Directory Configuration for Laravel
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/xaxino.conf \
    && echo '    Options -Indexes +FollowSymLinks' >> /etc/apache2/conf-available/xaxino.conf \
    && echo '    AllowOverride All' >> /etc/apache2/conf-available/xaxino.conf \
    && echo '    Require all granted' >> /etc/apache2/conf-available/xaxino.conf \
    && echo '</Directory>' >> /etc/apache2/conf-available/xaxino.conf \
    && echo 'SetEnvIf X-Forwarded-Proto https HTTPS=on' >> /etc/apache2/conf-available/xaxino.conf \
    && a2enconf xaxino

# Increase PHP memory & upload limits
RUN echo 'memory_limit = 512M' > /usr/local/etc/php/conf.d/xaxino-limits.ini \
    && echo 'upload_max_filesize = 64M' >> /usr/local/etc/php/conf.d/xaxino-limits.ini \
    && echo 'post_max_size = 64M' >> /usr/local/etc/php/conf.d/xaxino-limits.ini \
    && echo 'max_execution_time = 300' >> /usr/local/etc/php/conf.d/xaxino-limits.ini

WORKDIR /var/www/html
COPY . /var/www/html

# Create storage and cache directories before chown/chmod
RUN mkdir -p /var/www/html/core/storage/app/public \
             /var/www/html/core/storage/framework/cache/data \
             /var/www/html/core/storage/framework/sessions \
             /var/www/html/core/storage/framework/views \
             /var/www/html/core/storage/logs \
             /var/www/html/core/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/core/storage \
    && chmod -R 775 /var/www/html/core/bootstrap/cache \
    && chmod +x /var/www/html/start.sh

CMD ["/var/www/html/start.sh"]
