##################################
# DEPENDENCIES STAGE
##################################
FROM php:8.4-fpm-alpine3.21 AS dependencies

WORKDIR /var/www

# Install system packages with cache mount for faster rebuilds
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache \
        $PHPIZE_DEPS \
        curl-dev \
        libxml2-dev \
        oniguruma-dev \
        postgresql-dev \
        imagemagick-dev \
        icu-dev \
        libzip-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        libpng \
        libheif \
        libde265 \
        rabbitmq-c-dev

# Install PHP extensions in parallel where possible (except imagick)
RUN docker-php-ext-install -j$(nproc) \
        exif \
        pdo_pgsql \
        intl \
        zip \
        opcache

RUN --mount=type=cache,target=/tmp/pear/cache \
    pecl install \
        apcu \
        amqp \
        redis \
    && docker-php-ext-enable \
        apcu \
        amqp \
        redis

##################################
# BUILD STAGE
##################################
FROM php:8.4-fpm-alpine3.21 AS build

WORKDIR /var/www

ARG GROUP_ID=1337
ARG GROUP_NAME=www
ARG USER_ID=1337
ARG USER_NAME=www

RUN addgroup -g ${GROUP_ID} -S ${GROUP_NAME} && \
    adduser -u ${USER_ID} -D -S -G ${GROUP_NAME} ${USER_NAME}

# Install runtime packages with cache mount
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache \
        icu-dev \
        libpq \
        libzip \
        su-exec \
        fcgi \
        imagemagick \
        libjpeg-turbo \
        libwebp \
        libpng \
        libheif \
        libde265 \
        rabbitmq-c-dev

# Copy dependencies from the dependencies stage early
COPY --from=dependencies /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=dependencies /usr/local/etc/php /usr/local/etc/php
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN chmod u+s /sbin/su-exec

# Use production PHP configuration
RUN mv "${PHP_INI_DIR}/php.ini-production" "${PHP_INI_DIR}/php.ini"

# Copy custom configurations
COPY ./docker/queue-worker/php/conf.d/zzz-settings-override.ini /usr/local/etc/php/conf.d/zzz-settings-override.ini

# Install composer dependencie packages with cache mount
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache --virtual .composer-deps \
      git \
      openssh

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install composer dependencies with cache mount
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-interaction --no-scripts

# Copy application code after composer dependencies
COPY --chown="${GROUP_NAME}:${USER_NAME}" ./ ./

# Make startup script executable
RUN chmod +x "./docker/queue-worker/shell/php-fpm-healthcheck.sh"

##################################
# LOCAL ENVIRONMENT
##################################
FROM build AS local

ARG APP_RUNTIME_ENV
ENV APP_RUNTIME_ENV=${APP_RUNTIME_ENV}

COPY ./docker/queue-worker/php/conf.d/zzz-settings-override-local.ini /usr/local/etc/php/conf.d/zzz-settings-override.ini

# Optimize autoloader for local development
RUN composer dump-autoload \
      --optimize \
      --classmap-authoritative

# Set up dependencies for compilation
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        linux-headers

# Clean up the dependencies for compilation to reduce the image size
RUN apk del .build-deps

ENTRYPOINT ["sh", "./docker/queue-worker/shell/startup.sh"]

##################################
# TESTING ENVIRONMENT
##################################
FROM build AS testing

# Optimize autoloader for testing
RUN composer dump-autoload \
      --optimize \
      --classmap-authoritative

ENTRYPOINT ["sh", "./docker/queue-worker/shell/startup.sh"]

##################################
# PRODUCTION ENVIRONMENT
##################################
FROM build AS prod

# Install production dependencies and clean up
RUN composer install --no-interaction --optimize-autoloader --no-dev && \
    apk del .composer-deps

USER ${USER_NAME}

ENTRYPOINT ["sh", "./docker/queue-worker/shell/startup.sh"]
