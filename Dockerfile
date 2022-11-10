FROM php:8.1-apache-bullseye

ENV DEBIAN_FRONTEND noninteractive
ENV VIPS_VERSION=8.13.3
WORKDIR /usr/local/src

RUN export MAKEFLAGS="-j $(nproc)" \
 && apt-get update && apt-get install --no-install-recommends -y  \
    build-essential \
    libgirepository1.0-dev\
    meson \
    pkg-config \
    libglib2.0-dev \
    libexif-dev \
    libexpat1-dev \
    libffi-dev \
    libpoppler-glib-dev \
    libfreetype6-dev \
    liblcms2-dev \
    libimagequant-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    librsvg2-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    zip \
 && curl -sLO https://github.com/libvips/libvips/releases/download/v${VIPS_VERSION}/vips-${VIPS_VERSION}.tar.gz \
 && tar xf vips-${VIPS_VERSION}.tar.gz && cd vips-${VIPS_VERSION} \
 && meson build --prefix /usr/local --libdir lib \
 && cd build && meson compile && meson install && ldconfig \
 && cd ../.. && rm -R vips-${VIPS_VERSION} && rm vips-${VIPS_VERSION}.tar.gz\
 && docker-php-ext-configure zip \
 && docker-php-ext-install -j$(nproc) \
    ffi \
    opcache \
    zip \
 && echo 'ffi.enable=1' >> /usr/local/etc/php/conf.d/docker-php-ext-ffi.ini

WORKDIR /var/www
COPY ./ .
