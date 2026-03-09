##################################
# BASE STAGE (Extensions & OS Packages)
##################################
FROM php:8.4-fpm-alpine3.21 AS base

WORKDIR /var/www

# Copy the extension installer
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/bin/install-php-extensions

# Install ALL extensions
# Create a single layer that will be cached
RUN install-php-extensions \
    exif \
    pdo_pgsql \
    intl \
    zip \
    opcache \
    apcu \
    amqp \
    redis \
    imagick \
    gd \
    bcmath

# System utilities required for operation (runtime)
RUN apk add --no-cache \
    su-exec \
    fcgi \
    imagemagick \
    ghostscript \
    bash \
    icu-data-full

##################################
# BUILD STAGE (Composer & Code)
##################################
FROM base AS build

ARG GROUP_ID=1337
ARG GROUP_NAME=www
ARG USER_ID=1337
ARG USER_NAME=www

RUN addgroup -g ${GROUP_ID} -S ${GROUP_NAME} && \
    adduser -u ${USER_ID} -D -S -G ${GROUP_NAME} ${USER_NAME}

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy PHP configurations
COPY ./docker/app/php/conf.d/zzz-settings-override.ini /usr/local/etc/php/conf.d/zzz-settings-override.ini

# Install composer dependencie packages with cache mount
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache --virtual .composer-deps \
      git \
      openssh

# Copy composer files for dependency caching
COPY composer.json composer.lock ./

# Install composer dependencies with cache mount
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-interaction --no-scripts --no-autoloader

# Copy application code after composer dependencies
COPY --chown="${GROUP_NAME}:${USER_NAME}" ./ ./

# Finalizing the bootloader
RUN composer dump-autoload \
      --optimize \
      --classmap-authoritative

# Make startup script executable
RUN chmod +x "./docker/app/shell/php-fpm-healthcheck.sh"

##################################
# LOCAL ENVIRONMENT
##################################
FROM build AS local

ARG APP_RUNTIME_ENV
ENV APP_RUNTIME_ENV=${APP_RUNTIME_ENV}

# create folder for xdebug logs
RUN mkdir -p /var/log/xdebug && chmod 777 /var/log/xdebug

COPY ./docker/app/php/conf.d/zzz-settings-override-local.ini /usr/local/etc/php/conf.d/zzz-settings-override.ini

# Install XDebug
RUN install-php-extensions xdebug

# Optimize autoloader for local development
RUN composer dump-autoload \
      --optimize \
      --classmap-authoritative

ENTRYPOINT ["sh", "./docker/app/shell/startup.sh"]

##################################
# TESTING ENVIRONMENT
##################################
FROM build AS testing

# Optimize autoloader for testing
RUN composer dump-autoload \
      --optimize \
      --classmap-authoritative

ENTRYPOINT ["sh", "./docker/app/shell/startup.sh"]

##################################
# PRODUCTION ENVIRONMENT
##################################
FROM build AS prod

# Install production dependencies and clean up
RUN composer install --no-interaction --optimize-autoloader --no-dev && \
    chown ${GROUP_NAME}:${USER_NAME} /usr/local/etc/php-fpm.d/zz-docker.conf && \
    apk del .composer-deps

USER ${USER_NAME}

ENTRYPOINT ["sh", "./docker/app/shell/startup.sh"]
