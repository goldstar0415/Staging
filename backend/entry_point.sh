#!/usr/bin/env bash

echo "Project files are: " && ls -la

set -e
set -u

mkdir -p /data/tmp/php && chmod -R 777 /data/tmp/php
#&& chown -R 80:80 /data/
    # Put environment variables (including from Docker) in to php conf file to use in env()
#    env | sed "s/\(.*\)=\(.*\)/env[\1]='\2'/" > /data/conf/php-fpm-www-docker-env.conf && \
#    echo "Sleep 20s..." && sleep 20 && composer install && \
#    supervisorctl restart php-fpm && \
#    /config/bootstrap.sh


SUPERVISOR_PARAMS='-c /etc/supervisord.conf'


# Create directories for supervisor's UNIX socket and logs (which might be missing
# as container might start with /data mounted from another data-container).
mkdir -p /data/conf /data/run /data/logs
chmod 711 /data/conf /data/run /data/logs

if [ "$(ls /config/init/)" ]; then
  for init in /config/init/*.sh; do
    . $init
  done
fi

# Put environment variables (including from Docker) in to php conf file to use in the env()
env | sed "s/\(.*\)=\(.*\)/env[\1]='\2'/" | grep -v "''" > /data/conf/php-fpm-www-docker-env.conf

chown www:www /var/www/zoomtivity-backend/storage -R && chmod 777 /var/www/zoomtivity-backend/storage -R

echo "Sleep 20s..." && sleep 20 && composer install

# We have TTY, so probably an interactive container...
if test -t 0; then
  # Run supervisord detached...
  supervisord ${SUPERVISOR_PARAMS}

  # Some command(s) has been passed to container? Execute them and exit.
  # No commands provided? Run bash.
  if [[ $@ ]]; then
    eval $@
  else
    export PS1='[\u@\h : \w]\$ '
    /bin/bash
  fi

# Detached mode? Run supervisord in foreground, which will stay until container is stopped.
else
  # If some extra params were passed, execute them before.
  if [[ $@ ]]; then
    eval $@
  fi
  supervisord -n ${SUPERVISOR_PARAMS}
fi
