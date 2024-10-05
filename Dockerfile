FROM php:8.2-fpm

# Step 2: Install dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql

# Step 3: Set the working directory
WORKDIR /var/www/html/app

# Step 4: Copy the application files to the container
COPY . /var/www/html/app

# Step 5: Set up NGinx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Step 6: Make sure www-data can access the files
RUN chown -R www-data:www-data /var/www/html/app \
    && chmod -R 755 /var/www/html/app

# Step 7: Expose the ports
EXPOSE 80 9000

# Step 8: Define the command to start both PHP-FPM and NGinx
CMD service nginx start && php-fpm
