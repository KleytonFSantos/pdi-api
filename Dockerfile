FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y git libzip-dev unzip p7zip-full

# Install system dependencies
RUN apt-get update && apt-get install -y git libpq-dev librabbitmq-dev librabbitmq4

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql

# Install AMQP extension
RUN pecl install amqp && docker-php-ext-enable amqp


# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www