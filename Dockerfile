# Dockerfile pour Nailloux-club : conteneurisation
# Base : PHP 8.1 + Apache
# Installation des extensions PHP (pdo_mysql, gd, exif) et de Python3-Pillow

# 1. Image de base : PHP 8.1 avec Apache
FROM php:8.1-apache

# Install system dependencies
# 2. Mise à jour du cache et installation des paquets système essentiels
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
# 3. Installation de la bibliothèque Python Pillow pour l'extraction de métadonnées EXIF
RUN apt-get install -y python3-pil


# Configure PHP - Increase file upload limits
# 4. Copie du fichier de configuration PHP personnalisé (php.ini)
COPY php.ini /usr/local/etc/php/conf.d/php-custom.ini

# Enable Apache modules
# 5. Activation des modules Apache nécessaires (rewrite, headers)
RUN a2enmod rewrite headers

# Set working directory
# 6. Définition du répertoire de travail de l’application
WORKDIR /var/www/html

# Copy application files
# 7. Copie du code source de l'application dans le conteneur
COPY . /var/www/html/

# Use the Docker connection file
# 8. Configuration de la connexion à la base de données pour Docker
RUN cp /var/www/html/backend/db/connection.docker.new.php /var/www/html/backend/db/connection.php

# Configure Apache
# 9. Configuration du VirtualHost Apache (DocumentRoot, DirectoryIndex, etc.)
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
# 10. Attribution des permissions sur le répertoire de l'application
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Set ServerName globally to prevent Apache warnings
RUN echo "ServerName www.nailloux.lan" >> /etc/apache2/apache2.conf


# Expose port 80
# 11. Exposition du port HTTP (80)
EXPOSE 80
