FROM php:8.5.12-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip libicu-dev libpq-dev libzip-dev \
    && docker-php-ext-install intl pdo_pgsql zip \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY --from=ghcr.io/symfony-cli/symfony-cli:latest \
    /usr/local/bin/symfony /usr/local/bin/symfony

WORKDIR /workspace
