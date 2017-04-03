#!/bin/bash

set -e

createuser -s --replication logical_replication

psql -d zoomtivity -c "CREATE EXTENSION pglogical"

psql -d zoomtivity -c "SELECT pglogical.create_node(
        node_name := 'provider',
        dsn := 'host=107.150.34.138 port=5432 dbname=zoomtivity user=logical_replication'
);"
psql -d zoomtivity -c "SELECT pglogical.replication_set_add_all_tables('default', '{public}'::text[]);"
psql -d zoomtivity -c "SELECT pglogical.replication_set_remove_table('default', 'spatial_ref_sys');"
