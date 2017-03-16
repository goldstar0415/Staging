FROM million12/nginx-php:php70

MAINTAINER Wirnex <info@wirnex.com>

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    rm -rf /tmp/* /var/tmp/* /var/lib/apt/lists/* && \
    mkdir -p /data/tmp/php && chmod -R 777 /data/tmp/ && \
    yum install -y php70-php-pgsql

# todo fix postgresbuilder

#COPY . /data/www/default
WORKDIR /data/www/default
#RUN chown -R 80:80 /data/ && composer install --no-scripts
COPY entry_point.sh /config/
RUN chmod +x /config/entry_point.sh

ENTRYPOINT ["/config/entry_point.sh"]

EXPOSE 80
