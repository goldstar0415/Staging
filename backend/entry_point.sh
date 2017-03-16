#!/usr/bin/env bash

chmod -R 777 /data/tmp/php && chown -R 80:80 /data/ && composer install && \
# Put environment variables (including from Docker) in to php conf file to use in env()
env | sed "s/\(.*\)=\(.*\)/env[\1]='\2'/" > /data/conf/php-fpm-www-docker-env.conf && \
supervisorctl restart php-fpm && \
/config/bootstrap.sh
