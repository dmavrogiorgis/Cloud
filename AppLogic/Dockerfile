FROM php:7.3-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install zip
    
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer require hoa/websocket
#ENTRYPOINT ["/bin/bash", "-c", "composer require hoa/websocket"]

RUN mkdir /AppLogic

COPY ./AppLogic /AppLogic/

RUN cp -r /AppLogic/* /var/www/html/.