# web/Dockerfile
FROM php:7.4-apache

# Atualiza os pacotes e instala a lib necessária para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

# (Opcional) Habilita o mod_rewrite se for necessário
RUN a2enmod rewrite

# Se desejar copiar os arquivos da aplicação, descomente a linha abaixo
# COPY ../app/ /var/www/html/

EXPOSE 80
