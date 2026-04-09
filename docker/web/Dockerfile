ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-fpm-alpine

ARG WEB_INTERNAL_PORT=80
ENV WEB_INTERNAL_PORT=${WEB_INTERNAL_PORT}

# Install Nginx and runtime deps for Drupal.
RUN apk add --no-cache \
    nginx \
    fcgi \
    bash \
    gettext \
    icu-dev \
    libzip-dev \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
    pdo_mysql \
    mysqli \
    opcache \
    intl \
    zip \
    gd \
    && rm -rf /var/cache/apk/*

RUN mkdir -p /run/nginx /var/log/nginx /var/www/html /var/run/php \
    && chown -R nginx:nginx /run/nginx /var/log/nginx /var/run/php

COPY docker/web/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/web/php-fpm-socket.conf /usr/local/etc/php-fpm.d/zz-docker-socket.conf
COPY docker/web/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /var/www/html

EXPOSE ${WEB_INTERNAL_PORT}

ENTRYPOINT ["/entrypoint.sh"]