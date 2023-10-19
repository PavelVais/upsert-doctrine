# Dockerfile
FROM php:8.0-fpm

# Install PHP extensions, you may add more extensions if needed
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update \
    && apt-get install -y fish

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html
