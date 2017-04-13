FROM wirnex/postgis:1.2

MAINTAINER Egor Vorozhtsov <egor@vorozhtsov.com.ru>

ADD ./configs /pglogical-configs
COPY entrypoint.sh /usr/local/bin/

RUN \
    apt-get update && apt-get install -y wget gnupg nano mc && \
    echo "deb http://apt.postgresql.org/pub/repos/apt/ jessie-pgdg main" > /etc/apt/sources.list.d/pgdg.list && \
    wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - && \
    echo "deb [arch=amd64] http://packages.2ndquadrant.com/pglogical/apt/ jessie-2ndquadrant main" > /etc/apt/sources.list.d/2ndquadrant.list && \
    wget --quiet -O - http://packages.2ndquadrant.com/pglogical/apt/AA7A6805.asc | apt-key add - && \
    apt-get update && apt-get install -y postgresql-9.6-pglogical && \
    apt-get purge -y --auto-remove wget && rm -rf /var/lib/apt/lists/* && \
    chmod +x /usr/local/bin/entrypoint.sh && ln -s /usr/local/bin/entrypoint.sh /

ENTRYPOINT ["entrypoint.sh"]

CMD ["postgres"]
