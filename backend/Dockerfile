FROM million12/nginx-php:php70

MAINTAINER Wirnex <info@wirnex.com>

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# php extensions RUN docker-php-ext-install bcmath ctype dom gettext intl json mbstring mcrypt mysqli opcache pcntl pdo pdo_mysql pdo_pgsql pdo_sqlite simplexml shmop xml zip
# cleanup RUN apt-get -y autoremove && apt-get autoclean && apt-get clean && \
RUN rm -rf /tmp/* /var/tmp/* /var/lib/apt/lists/*
# add conf files for supervisor, php-fpm and php ADD config-files /

# todo fix postgresbuilder
RUN mkdir -p /data/tmp/php && chmod 777 /data/tmp/php

COPY . /data/www/default
COPY .env /data/www/default
WORKDIR /data/www/default
RUN composer install --no-scripts
COPY entry_point.sh /config/
RUN chmod +x /config/entry_point.sh

#CMD composer run-script --no-dev post-install-cmd
# && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

ENTRYPOINT ["/config/entry_point.sh"]

EXPOSE 80