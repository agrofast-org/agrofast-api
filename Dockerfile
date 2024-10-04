# FROM nginx:1.27.1

# EXPOSE 80

# COPY . /var/www/html/app
# COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# FROM php:8.2-fpm

# EXPOSE 9000

# COPY . /var/www/html/app

# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# WORKDIR /var/www/html/app
# RUN composer install --ignore-platform-reqs
