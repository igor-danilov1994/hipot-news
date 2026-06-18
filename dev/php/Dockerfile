FROM php:8.2-fpm

# Системные зависимости для PHP-расширений, нужных Bitrix
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libexif-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        mysqli \
        pdo_mysql \
        gd \
        zip \
        intl \
        mbstring \
        bcmath \
        exif \
        opcache \
        sockets \
    && rm -rf /var/lib/apt/lists/*

# Конфигурация PHP для Bitrix
COPY bitrix.ini /usr/local/etc/php/conf.d/bitrix.ini
