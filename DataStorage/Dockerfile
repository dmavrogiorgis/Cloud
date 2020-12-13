FROM php:7.3-apache

RUN apt-get update && apt-get install -y\
    libssl-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install zip

RUN pecl install mongodb\
    && echo "extension=mongodb.so" >> /usr/local/etc/php/conf.d/ext-mongodb.ini    

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer require mongodb/mongodb
#ENTRYPOINT ["/bin/bash", "-c", "composer require mongodb/mongodb"]
RUN mkdir /DataStorage

COPY ./DataStorage /DataStorage/

RUN cp -r /DataStorage/* /var/www/html/.

#CMD ["composer", "require", "mongodb/mongodb"]