# Fase de construcción de Composer
FROM php:8.2-cli AS build

# Instalación de dependencias necesarias para Composer
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql

# Copiamos Composer desde su imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definimos el directorio de trabajo y copiamos los archivos de la aplicación
WORKDIR /app
COPY . /app
RUN composer update
# Instalamos las dependencias de Composer
RUN #composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

# Fase de desarrollo
FROM php:8.2-apache-buster AS dev

# Variables de entorno para desarrollo
ENV APP_ENV=dev
ENV APP_DEBUG=true
ENV COMPOSER_ALLOW_SUPERUSER=1

# Instalación de extensiones de PHP y habilitación de módulos de Apache
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql

# Copiamos el contenido de la aplicación al contenedor
COPY . /var/www/html/
COPY --from=build /usr/bin/composer /usr/bin/composer

# Ejecutamos la instalación de Composer en modo de desarrollo
RUN #composer install --prefer-dist --no-interaction

# Configuramos Apache
COPY docker-compose/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Configuración de Laravel
#RUN php artisan config:cache && \
#    php artisan route:cache && \
#    chmod -R 777 /var/www/html/storage/ && \
#    chown -R www-data:www-data /var/www/ && \
#    a2enmod rewrite

RUN chmod -R 777 /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite

# Fase de producción
FROM php:8.2-apache-buster AS production

# Variables de entorno para producción
ENV APP_ENV=production
ENV APP_DEBUG=false

# Configuración y optimización de PHP para producción
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-configure opcache --enable-opcache && \
    docker-php-ext-install pdo pdo_mysql

# Copiamos la configuración de opcache
COPY docker-compose/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copiamos la aplicación desde la fase de build
COPY --from=build /app /var/www/html
COPY docker-compose/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Establecemos el directorio de trabajo
WORKDIR /var/www/html

# Configuramos Laravel en producción
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan key:generate && \
    chmod -R 777 /var/www/html/storage/uploads/template-certificate/ && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite
