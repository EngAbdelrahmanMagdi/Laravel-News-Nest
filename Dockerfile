# Use PHP 8.1 FPM (FastCGI Process Manager) based on Alpine Linux  
FROM php:8.1-fpm-alpine

# Install Composer  
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install necessary dependencies:
RUN set -ex \
    && apk --no-cache add postgresql-dev nodejs yarn npm caddy \
    && docker-php-ext-install pdo pdo_pgsql

# Set the working directory inside the container     
WORKDIR /var/www/html