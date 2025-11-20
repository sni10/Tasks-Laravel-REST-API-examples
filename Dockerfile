FROM php:8.2-fpm

#ENV APP_ENV=local \
#    APP_DEBUG=true \
#    DB_HOST=mysql \
#    DB_DATABASE=task_management \
#    DB_USERNAME=root \
#    DB_PASSWORD=root \
#    PHP_IDE_CONFIG="serverName=local-debug"


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
    && docker-php-ext-install mbstring exif pcntl bcmath gd pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN pecl install redis && docker-php-ext-enable redis \
    && pecl install xdebug && docker-php-ext-enable xdebug

RUN docker-php-ext-install mbstring exif pcntl bcmath gd pdo pdo_mysql zip

#COPY ./nginx/php.ini /usr/local/etc/php/conf.d/custom-php.ini
#COPY ./nginx/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

COPY . /var/www

WORKDIR /var/www

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

RUN #composer install --no-interaction --prefer-dist --optimize-autoloader

USER www-data

CMD ["php-fpm"]
