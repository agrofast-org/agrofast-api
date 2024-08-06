FROM php:8.1-fpm-alpine

EXPOSE 3000
EXPOSE 8040

USER root

RUN mkdir /app

RUN apk update
RUN apk add bash
RUN apk add openrc
RUN mkdir /run/openrc && touch /run/openrc/softlevel
RUN rc-status

RUN apk add nginx
RUN rc-update add nginx

RUN apk add php
RUN apk add php-fpm
RUN apk add php-pdo
RUN apk add php-curl
RUN apk add php-pgsql
RUN apk add php-pdo_pgsql
