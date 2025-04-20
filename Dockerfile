# ----------------------------------------
# Dockerfile pour Nailloux-club : conteneurisation
# Base : PHP 8.1 + Apache
# Installation des extensions PHP (pdo_mysql, gd, exif) et de Python3-Pillow
# ----------------------------------------

FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    python3 \
    python3-pip \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j$(nproc) pdo_mysql mysqli zip exif gd

# Install Python Pillow via apt (system pip install is blocked)
RUN apt-get install -y python3-pil


# Configure PHP - Increase file upload limits
COPY php.ini /usr/local/etc/php/conf.d/php-custom.ini

# Enable Apache modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Use the Docker connection file
RUN cp /var/www/html/backend/db/connection.docker.new.php /var/www/html/backend/db/connection.php

# Configure Apache
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    DirectoryIndex index.php\n\
    <Directory "/var/www/html">\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80
