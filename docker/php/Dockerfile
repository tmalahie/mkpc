FROM php:8-apache

RUN docker-php-ext-install pdo_mysql

RUN apt-get update && \
    apt-get install -y \
        build-essential exiftool libssl-dev \
        zlib1g-dev libpng-dev libjpeg-dev \
        libfreetype6-dev locales libicu-dev

# Add i10n stuff + enable some locales
RUN echo >> /etc/locale.gen '\n\
fr_FR.UTF-8 UTF-8\n\
en_GB.UTF-8 UTF-8\n\
es_ES.UTF-8 UTF-8\n'
RUN locale-gen

RUN docker-php-ext-configure exif && docker-php-ext-install exif && docker-php-ext-enable exif
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install gd
RUN docker-php-ext-install gettext intl
RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY conf.d/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN mkdir -p /var/www/html/images/avatars
RUN chmod 777 /var/www/html/images/avatars
RUN mkdir -p /var/www/html/images/uploads
RUN chmod 777 /var/www/html/images/uploads
RUN mkdir -p /var/www/html/images/sprites/uploads
RUN chmod 777 /var/www/html/images/sprites/uploads
RUN mkdir -p /var/www/html/images/creation_icons
RUN chmod 777 /var/www/html/images/creation_icons
