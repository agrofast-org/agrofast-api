#!/bin/sh
set -e

echo "Copiando arquivos de configuração para o diretório compartilhado..."
cp -v /app/conf.d/* /mnt/nginx-conf/

echo "Recarregando o Nginx global..."
docker exec nginx nginx -s reload

echo "Injeção concluída. Saindo."
