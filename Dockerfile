FROM php:8.4-fpm

WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl pdo_mysql opcache zip gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copy composer files first
COPY composer.json composer.lock /var/www/html/

# Install dependencies (no scripts yet to avoid bin/console error)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy the rest of the app
COPY . /var/www/html

# Run post-install scripts now that bin/console exists
RUN composer run-script post-install-cmd || true

# Ensure cache/logs folder exist
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log && \
    chown -R www-data:www-data /var/www/html/var

EXPOSE 9000
CMD ["php-fpm"]
