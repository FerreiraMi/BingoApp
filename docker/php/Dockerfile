FROM php:8.1-fpm-bullseye

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libssl-dev \
    libzip-dev \
    zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install sockets zip

RUN pecl install mongodb && docker-php-ext-enable mongodb

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia tudo de uma vez (inclusive composer.json e server.php)
COPY backend/ ./

# Agora o Composer encontra os arquivos e instala corretamente
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

RUN chown -R www-data:www-data /var/www/html