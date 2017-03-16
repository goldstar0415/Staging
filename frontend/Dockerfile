FROM nginx:alpine

MAINTAINER Egor Vorozhtsov <egor@vorozhtsov.com.ru>

COPY ./ /var/www/zoomtivity

RUN \
  echo "Insalling required Alpine packages..." && apk update && apk add --no-cache nodejs python make g++ git && \
  echo "Removing default nginx config..." && rm /etc/nginx/conf.d/default.conf && \
  echo "Installing global NPM packages..." && npm install -g gulp bower && \
  echo "Installing local NPM packages..." && cd /var/www/zoomtivity && npm install && \
  echo "Installing Bower packages..." && bower install --allow-root && \
  echo "Building project..." && npm run build

COPY nginx-vhost.conf /etc/nginx/conf.d/

EXPOSE 80 81
