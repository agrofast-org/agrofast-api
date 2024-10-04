FROM nginx:1.27.1

EXPOSE 8070

RUN apt update
RUN apt install -y php-fpm php-pdo php-curl php-pgsql php-pdo-pgsql
RUN apt clean 
RUN rm -rf /var/lib/apt/lists/*

COPY ./nginx.conf /etc/nginx/conf.d/default.conf
RUN mkdir -p /var/www/html/agrofast
COPY ./* /var/www/html/agrofast

CMD ["nginx", "-g", "daemon off;"]