FROM php:8-apache

RUN docker-php-ext-install pdo_mysql

RUN apt-get update
RUN apt-get install -y build-essential exiftool libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev

RUN docker-php-ext-configure exif && docker-php-ext-install exif && docker-php-ext-enable exif
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install gd