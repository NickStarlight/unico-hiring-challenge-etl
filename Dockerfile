FROM php:8.0-cli
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && apt-get install -y libzip-dev libpq-dev git \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

ADD . /usr/src/etl
WORKDIR /usr/src/etl

RUN composer install

CMD [ "php", "./index.php", "import-fairs" ]