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
RUN npx vite build \
  && test -f /app/public/build/manifest.json

FROM php:8.3-apache-bookworm AS app
WORKDIR /app

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    unzip \
  && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
  && docker-php-ext-install -j"$(nproc)" \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pdo \
    pdo_mysql \
    zip \
  && a2enmod rewrite headers expires \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer-build /app /app
COPY --from=node-build /app/public/build /app/public/build
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
  && mkdir -p /app/storage /app/bootstrap/cache \
  && chown -R www-data:www-data /app/storage /app/bootstrap/cache

ENV APP_ENV=production
ENV APP_DEBUG=false
EXPOSE 80

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]
