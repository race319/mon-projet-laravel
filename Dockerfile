FROM php:8.2-apache

# Installer dépendances système
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev

# Extensions PHP pour Laravel
RUN docker-php-ext-install pdo pdo_mysql zip gd

# Activer rewrite
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier le projet
WORKDIR /var/www/html
COPY . .

# Permissions Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
