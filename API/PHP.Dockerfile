FROM php:fpm

WORKDIR /api

RUN docker-php-ext-install pdo pdo_mysql
