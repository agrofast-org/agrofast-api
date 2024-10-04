FROM nginx:1.27.1

EXPOSE 8070

RUN apt update && \
  apt install -y php-pdo php-curl php-pgsql php-pdo_pgsql && \
  apt clean && \
  rm -rf /var/lib/apt/lists/*

COPY ./nginx.conf /etc/nginx/conf.d/default.conf
COPY ./* /var/www/html/agrofast

CMD ["nginx", "-g", "daemon off;"]
