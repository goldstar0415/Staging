FROM million12/nginx-php:php70

MAINTAINER Wirnex <info@wirnex.com>

COPY ./ /var/www/zoomtivity-backend
COPY entry_point.sh /config/
COPY nginx-vhost.conf /etc/nginx/hosts.d/zoomtivity-backend.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    rm -rf /tmp/* /var/tmp/* /var/lib/apt/lists/* && \
    yum install -y php70-php-pgsql cronie nano mc && \
    chmod +x /config/entry_point.sh

WORKDIR /var/www/zoomtivity-backend

ENTRYPOINT ["/config/entry_point.sh"]

EXPOSE 80
