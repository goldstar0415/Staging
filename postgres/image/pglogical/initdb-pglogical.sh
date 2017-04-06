#!/bin/sh

set -e
set -x

REPLICATION_USER="logical_replication"
REPLICATION_HOST="database"
REPLICATION_PORT="5432"
REPLICATION_PROVIDER="provider"

export PGUSER="$POSTGRES_USER"

createuser --superuser --replication ${REPLICATION_USER}

psql -d ${POSTGRES_DB} -c "CREATE EXTENSION pglogical;"

psql -d ${POSTGRES_DB} -c "SELECT pglogical.create_node(
    node_name := '${REPLICATION_PROVIDER}',
    dsn := 'host=${REPLICATION_HOST} port=${REPLICATION_PORT} dbname=${POSTGRES_DB} user=${REPLICATION_USER}'
);"

psql -d ${POSTGRES_DB} -c "SELECT pglogical.replication_set_add_all_tables('default', '{public}'::text[]);"
# fixme - spatial_ref_sys doesn't exist
#psql -d ${POSTGRES_DB} -c "SELECT pglogical.replication_set_remove_table('default', 'spatial_ref_sys');"
