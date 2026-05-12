# Use PHP with Apache
FROM php:8.2-apache

# 1. Install system dependencies for Composer and MySQL
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip

# 2. Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Set working directory
WORKDIR /var/www/html

# 4. Copy project files
COPY . .

# 5. Install PHP dependencies
# This runs composer install automatically on Render
RUN composer install --no-interaction --optimize-autoloader

# 6. Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# 7. Enable Apache rewrite (standard for PHP apps)
RUN a2enmod rewrite

EXPOSE 80