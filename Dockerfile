FROM oven/bun:latest as client

WORKDIR /app

COPY . .

RUN bun install
RUN bun run dist

FROM php:8.3-apache as dist

LABEL org.opencontainers.image.source="https://github.com/karlomikus/zapis"
LABEL org.opencontainers.image.description="Zapis is a simple notes app"
LABEL org.opencontainers.image.licenses=MIT

RUN apt-get update && apt-get install -y \
        git unzip sqlite3 \
        && apt-get clean && rm -rf /var/lib/apt/lists/*

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions intl

COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/notesapp/public

WORKDIR /var/www/notesapp

COPY . .
COPY --from=client /app/public ./public

RUN composer install --no-dev --optimize-autoloader

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN a2enmod rewrite
RUN a2enmod actions