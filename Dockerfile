FROM php:8.1-fpm-alpine

RUN apk add --no-cache \
    libzip-dev \
    vips-dev \
 && docker-php-ext-configure zip \
 && docker-php-ext-install -j$(nproc) \
    ffi \
    opcache \
    zip \
 && echo 'ffi.enable=1' >> /usr/local/etc/php/conf.d/docker-php-ext-ffi.ini
