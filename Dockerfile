FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd zip pdo pdo_mysql

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www

EXPOSE 9000
CMD ["php-fpm"]
