FROM nginx:1.27.1

EXPOSE 80

RUN apt update
RUN apt install -y php php-fpm php-pdo php-curl php-pgsql php-pdo-pgsql
RUN apt clean 
RUN rm -rf /var/lib/apt/lists/*

COPY ./nginx.conf /etc/nginx/conf.d/default.conf
RUN mkdir -p /var/www/html
COPY ./* /var/www/html

RUN service php8.2-fpm restart

CMD ["nginx", "-g", "daemon off;"]
