FROM oven/bun:latest AS client

WORKDIR /app

COPY . .

RUN bun install
RUN bun run dist

FROM serversideup/php:8.4-fpm-apache AS base

LABEL org.opencontainers.image.source="https://github.com/karlomikus/zapis"
LABEL org.opencontainers.image.description="Zapis is a simple notes app"
LABEL org.opencontainers.image.licenses=MIT

USER root

RUN apt-get update && apt-get install -y \
        git unzip sqlite3 \
        && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions intl

ENV APACHE_DOCUMENT_ROOT=/var/www/notesapp/public
ENV APP_ENV=prod

USER www-data

FROM base AS develop

USER root

ARG USER_ID
ARG GROUP_ID

RUN docker-php-serversideup-set-id www-data $USER_ID:$GROUP_ID && \
    \
    docker-php-serversideup-set-file-permissions --owner $USER_ID:$GROUP_ID --service apache

USER www-data

FROM base AS production

ENV PHP_OPCACHE_ENABLE=1

WORKDIR /var/www/notesapp

COPY --chown=www-data:www-data . .
COPY --from=client --chown=www-data:www-data /app/public ./public
RUN mkdir var

RUN composer install --no-dev --optimize-autoloader

RUN a2enmod rewrite
RUN a2enmod actions
