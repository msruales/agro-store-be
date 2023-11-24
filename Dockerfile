FROM composer:latest as build
COPY . /app/
RUN composer update --prefer-dist --no-dev --optimize-autoloader --no-interaction

FROM php:8.1-apache-buster as dev

ENV APP_ENV=dev
ENV APP_DEBUG=true
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y zip
RUN docker-php-ext-install pdo pdo_mysql

COPY . /var/www/html/
COPY --from=build /usr/bin/composer /usr/bin/composer
RUN composer install --prefer-dist --no-interaction

COPY docker-compose/apache/000-default.conf /etc/apache2/sites-available/000-default.conf


RUN php artisan config:cache && \
    php artisan route:cache && \
    chmod 777 -R /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite

FROM php:8.2-apache-buster as production

ENV APP_ENV=production
ENV APP_DEBUG=false

RUN docker-php-ext-configure opcache --enable-opcache && \
    docker-php-ext-install pdo pdo_mysql
COPY docker-compose/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY --from=build /app /var/www/html
COPY docker-compose/apache/000-default.conf /etc/apache2/sites-available/000-default.conf


WORKDIR /var/www/html

RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan key:generate && \
    chmod 777 -R /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite

