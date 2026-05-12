FROM php:8.3-fpm

ARG USER_ID=1000
ARG GROUP_ID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        ca-certificates \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

RUN if ! getent group ${GROUP_ID} > /dev/null; then \
        groupadd --gid ${GROUP_ID} app; \
    fi \
    && useradd --uid ${USER_ID} --gid ${GROUP_ID} --shell /bin/bash --create-home app

WORKDIR /var/www/html

RUN chown -R ${USER_ID}:${GROUP_ID} /var/www/html

USER app

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]
