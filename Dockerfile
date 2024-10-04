FROM nginx:1.27.1

EXPOSE 80

RUN apt update && apt install -y curl && apt clean && rm -rf /var/lib/apt/lists/*

COPY ./nginx.conf /etc/nginx/conf.d/default.conf
RUN mkdir -p /var/www/html
COPY . /var/www/html

CMD ["nginx", "-g", "daemon off;"]
