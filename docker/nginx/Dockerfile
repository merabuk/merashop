FROM nginx:stable-alpine

WORKDIR /var/www

COPY ./docker ./docker
COPY ./docker/nginx/etc/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/nginx/etc/gzip.conf /etc/nginx/conf.d/gzip.conf

ARG APP_RUNTIME_ENV
ENV APP_ENV=${APP_RUNTIME_ENV}

ENTRYPOINT ["sh", "./docker/nginx/shell/startup.sh"]
