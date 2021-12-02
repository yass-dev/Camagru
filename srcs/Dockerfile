FROM php:8.0.2-apache

RUN apt-get update && apt-get upgrade -y
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
EXPOSE 80