FROM php:8.1-fpm-alpine

# Cài đặt PostgreSQL và các tiện ích cần thiết
RUN apk update && apk add --no-cache \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Cài đặt APCu
RUN apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .phpize-deps

RUN echo "apc.enable_cli = 1" >> /usr/local/etc/php/conf.d/custom.ini

CMD ["php-fpm"]