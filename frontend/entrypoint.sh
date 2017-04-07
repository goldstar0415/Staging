#!/bin/sh

echo "[development] Configuring NPM packages..." && \
    cd /var/www/zoomtivity-dev && \
    mkdir -p ./node_modules && \
    cp -r /var/www/zoomtivity/node_modules .

echo "[development] Configuring Bower packages..." && \
    mkdir -p ./bower_components && \
    cp -r /var/www/zoomtivity/bower_components .

echo "Starting Nginx..." && nginx -g 'daemon off;'

