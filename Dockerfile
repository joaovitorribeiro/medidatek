FROM composer:2 AS composer-build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts
COPY . .
RUN composer dump-autoload --optimize --no-scripts

FROM node:20-alpine AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js tsconfig.json postcss.config.js tailwind.config.js ./
COPY --from=composer-build /app/vendor/tightenco/ziggy ./vendor/tightenco/ziggy
RUN npx vite build

FROM php:8.3-fpm-bookworm AS app
WORKDIR /var/www/html

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    libicu-dev \
    libsqlite3-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    pkg-config \
    unzip \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" \
    bcmath \
    exif \
    gd \
    intl \
    pcntl \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    zip \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer-build /app /var/www/html
COPY --from=node-build /app/public/build /var/www/html/public/build
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
  && mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
  && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
