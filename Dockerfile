FROM php:8.2-fpm

ARG BUILD_ENV=prod

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
    && docker-php-ext-install mbstring exif pcntl bcmath gd pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN pecl install redis && docker-php-ext-enable redis \
    && pecl install xdebug && docker-php-ext-enable xdebug

WORKDIR /var/www

COPY composer.json composer.lock /var/www/

RUN if [ "$BUILD_ENV" = "test" ]; then \
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts; \
    else \
        composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts; \
    fi

COPY . /var/www

RUN if [ "$BUILD_ENV" = "test" ]; then \
        cp docker/config-envs/test/php.ini /usr/local/etc/php/conf.d/custom-php.ini; \
    fi

RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

USER www-data

CMD ["php-fpm"]
