FROM php:7.4-cli
WORKDIR /var/www/html

# Instala dependências necessárias, incluindo extensões do PHP e unzip
RUN apt-get update && apt-get install -y \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql sockets

# Copia os arquivos de composer e instala as dependências
COPY composer.json ./
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install

# Copia o restante dos arquivos do projeto para o container
COPY . .

CMD [ "php", "websocket_server.php" ]
