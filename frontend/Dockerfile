FROM nginx:alpine

MAINTAINER Egor Vorozhtsov <egor@vorozhtsov.com.ru>

COPY ./ /var/www/zoomtivity
COPY ./ /var/www/zoomtivity-dev

RUN \
  echo "Project files are: " && ls -las /var/www/zoomtivity && ls -las /var/www/zoomtivity-dev && \
  echo "Installing required Alpine packages..." && apk update && apk add --no-cache nodejs python make g++ git nano mc bash nasm autoconf automake libpng-dev libtool && \
  echo "Removing default nginx config..." && rm /etc/nginx/conf.d/default.conf && \
  echo "Installing global NPM packages..." && npm install -g gulp bower && \
  echo "Installing local NPM packages..." && cd /var/www/zoomtivity && npm install && \
  echo "Installing Bower packages..." && bower install --allow-root && \
  echo "Building project..." && npm run build && \
  echo "Configuring entrypoint..." && chmod +x ./entrypoint.sh && \
  echo "Project files are: " && ls -las /var/www/zoomtivity

COPY nginx-vhost.conf /etc/nginx/conf.d/

WORKDIR /var/www/zoomtivity-dev

EXPOSE 80 81

ENTRYPOINT ["/var/www/zoomtivity/entrypoint.sh"]
