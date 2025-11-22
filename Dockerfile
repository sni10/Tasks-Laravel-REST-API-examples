FROM php:8.2-fpm

ARG APP_ENV

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    procps \
    net-tools \
    lsof \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    curl \
    default-mysql-client \
    && docker-php-ext-install mbstring exif pcntl bcmath gd pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN pecl install redis && docker-php-ext-enable redis

RUN if [ "$APP_ENV" = "test" ]; then \
        pecl install xdebug && docker-php-ext-enable xdebug; \
    fi

WORKDIR /var/www

COPY composer.json composer.lock /var/www/

RUN if [ "$APP_ENV" = "test" ]; then \
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts; \
    else \
        composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts; \
    fi

COPY . /var/www

COPY docker/nginx/${APP_ENV}/php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY docker/nginx/${APP_ENV}/php-fpm.conf /usr/local/etc/php-fpm.d/zz-custom.conf

RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www-data

ENTRYPOINT ["entrypoint.sh"]
