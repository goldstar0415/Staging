FROM wirnex/postgis:1.2

MAINTAINER Egor Vorozhtsov <egor@vorozhtsov.com.ru>

RUN \
    apt-get update && apt-get install -y wget gnupg && \
    echo "deb http://apt.postgresql.org/pub/repos/apt/ jessie-pgdg main" > /etc/apt/sources.list.d/pgdg.list && \
    wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - && \
    echo "deb [arch=amd64] http://packages.2ndquadrant.com/pglogical/apt/ jessie-2ndquadrant main" > /etc/apt/sources.list.d/2ndquadrant.list && \
    wget --quiet -O - http://packages.2ndquadrant.com/pglogical/apt/AA7A6805.asc | apt-key add - && \
    apt-get update && apt-get install -y postgresql-9.6-pglogical && \
    apt-get purge -y --auto-remove wget && rm -rf /var/lib/apt/lists/*

RUN echo "local     replication         logical_replication         trust"                  >> /usr/share/postgresql/9.6/pg_hba.conf.sample
RUN echo "host      replication         logical_replication         172.18.0.0/16   trust"  >> /usr/share/postgresql/9.6/pg_hba.conf.sample
RUN echo "host      replication         logical_replication         ::1/128         trust"  >> /usr/share/postgresql/9.6/pg_hba.conf.sample
RUN echo "local     zoomtivity          logical_replication         trust"                  >> /usr/share/postgresql/9.6/pg_hba.conf.sample
RUN echo "host      zoomtivity          logical_replication         172.18.0.0/16   trust"  >> /usr/share/postgresql/9.6/pg_hba.conf.sample
RUN echo "host      zoomtivity          logical_replication         ::1/128         trust"  >> /usr/share/postgresql/9.6/pg_hba.conf.sample

RUN echo "shared_preload_libraries = 'pglogical'" >> /usr/share/postgresql/postgresql.conf.sample
RUN echo "wal_level = 'logical'" >> /usr/share/postgresql/postgresql.conf.sample
RUN echo "max_wal_senders = 20" >> /usr/share/postgresql/postgresql.conf.sample
RUN echo "max_replication_slots = 20" >> /usr/share/postgresql/postgresql.conf.sample

COPY ./initdb-pglogical.sh /docker-entrypoint-initdb.d/pglogical.sh
