FROM php:8.1-apache

RUN docker-php-ext-install pdo_mysql
RUN apt-get update
RUN apt-get install -y build-essential exiftool libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev

RUN docker-php-ext-configure exif && docker-php-ext-install exif && docker-php-ext-enable exif
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install gd

RUN a2enmod rewrite proxy proxy_http

COPY config/httpd.conf /etc/apache2/sites-available/000-default.conf

RUN mkdir -p /var/www/html/images/avatars
RUN chmod 777 /var/www/html/images/avatars
RUN mkdir -p /var/www/html/images/uploads
RUN chmod 777 /var/www/html/images/uploads
RUN mkdir -p /var/www/html/images/sprites/uploads
RUN chmod 777 /var/www/html/images/sprites/uploads
RUN mkdir -p /var/www/html/images/creation_icons
RUN chmod 777 /var/www/html/images/creation_icons