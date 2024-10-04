FROM nginx:1.27.1

EXPOSE 8070

RUN apt update
RUN apt install -y php-pdo
RUN apt install -y php-curl
RUN apt install -y php-pgsql
RUN apt install -y php-pdo-pgsql
RUN apt clean 
RUN rm -rf /var/lib/apt/lists/*

COPY ./nginx.conf /etc/nginx/conf.d/default.conf
COPY ./* /var/www/html/agrofast

CMD ["nginx", "-g", "daemon off;"]
