FROM php:8.0-cli

RUN apt-get update && apt-get install -y libzip-dev libpq-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

WORKDIR /usr/src/etl

CMD [ "php", "./index.php", "import-fairs" ]