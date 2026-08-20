FROM php:8.3-fpm-alpine

# --- System dependencies ---
RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    zlib-dev \
    nodejs \
    npm \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    nginx \
    supervisor

# --- PHP extensions ---
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath intl gd opcache

# --- Composer ---
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer config -g preferred-install dist \
    && composer config -g store-auths false

# --- Opcache tuning for production ---
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "upload_max_filesize=20M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=20M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

# --- Copy application source ---
COPY . .

# --- Install PHP & Node dependencies, build frontend assets ---
RUN for i in 1 2 3 4 5; do \
      echo "composer install attempt $i"; \
      if composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts; then \
        break; \
      fi; \
      if [ $i -eq 5 ]; then exit 1; fi; \
      echo "Composer install attempt $i failed, waiting 15s..."; \
      sleep 15; \
    done \
    && npm install --legacy-peer-deps \
    && npm run build

# --- Laravel bootstrap (clear stale caches, then discover packages) ---
RUN php artisan clear-compiled \
    && php artisan package:discover --ansi

# --- Permissions ---
RUN chmod +x /var/www/html/scripts/railway-start.sh \
    && chmod +x /var/www/html/scripts/write_env.php \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# --- Log directories ---
RUN mkdir -p /var/log/nginx /var/log \
    && touch /var/log/queue-worker.log /var/log/php-fpm.log /var/log/supervisord.log

# --- Copy Nginx config into place ---
COPY nginx.conf /etc/nginx/http.d/default.conf

# --- PHP-FPM: run as www-data ---
RUN sed -i 's/user = www-data/user = www-data/' /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true \
    && sed -i 's/group = www-data/group = www-data/' /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true

EXPOSE 10000

CMD ["/var/www/html/scripts/railway-start.sh"]
