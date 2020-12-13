FROM php:7.3-apache

RUN apt-get update && apt-get install -y

RUN mkdir /WebApp

COPY ./WebApp /WebApp/

RUN cp -r /WebApp/* /var/www/html/

#volumes:
#      - ./Data-Storage:/var/www/html/