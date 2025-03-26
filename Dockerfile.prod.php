FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zlib1g-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install sockets

COPY . /var/www/app

WORKDIR /var/www/app

RUN chown -R www-data:www-data /var/www/app \
    && chmod -R 775 /var/www/app/storage

COPY --from=composer:2.6.5 /usr/bin/composer /usr/local/bin/composer

RUN composer install --no-plugins --no-scripts || composer update --no-plugins --no-scripts

EXPOSE 9000

CMD ["php-fpm"]
