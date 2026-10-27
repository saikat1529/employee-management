# PHP 8.4 command-line image used to run the Demo Portal (Laravel 13 + Filament 5)
FROM php:8.4-cli

# System packages needed by the PHP extensions and Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        libzip-dev \
        libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions: zip, intl, bcmath, exif.
# pdo_sqlite and sqlite3 are already built into the official php image; the check below confirms them.
RUN docker-php-ext-install zip intl bcmath exif \
    && php -m | grep -qi "pdo_sqlite" \
    && php -m | grep -qi "sqlite3"

# Composer, copied from the official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy the project, then install PHP dependencies (dev packages included for this demo)
COPY . .
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
