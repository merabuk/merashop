FROM nginx:stable-alpine

WORKDIR /var/www

COPY ./docker ./docker
COPY ./docker/nginx/etc/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/nginx/etc/gzip.conf /etc/nginx/conf.d/gzip.conf

ENTRYPOINT ["sh", "./docker/nginx/shell/startup.sh"]
