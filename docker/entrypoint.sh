#!/bin/bash
set -e

# Wait for MySQL
until mysqladmin ping -h"${DB_HOST}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent; do
    echo "Waiting for MySQL..."
    sleep 2
done

# Run migrations
if [ "$APP_ENV" = "test" ]; then
    php artisan migrate:fresh --force
else
    php artisan migrate --force
fi

# Start php-fpm
exec php-fpm
