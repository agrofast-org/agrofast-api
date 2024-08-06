#!/usr/bin/env bash

# Remove Old Image
docker rm -f agrofast

# No Cache Build
docker build --no-cache -t agrofast -f docker/nginx/Dockerfile .

