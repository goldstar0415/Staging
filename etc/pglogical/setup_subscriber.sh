#!/bin/bash

set -e

createuser -s --replication logical_replication

psql -d zoomtivity_rep -c 'CREATE EXTENSION pglogical'

psql -d zoomtivity_rep -c "SELECT pglogical.create_node( node_name := 'subscriber', dsn := 'host=23.92.71.145 port=5432 dbname=zoomtivity_rep user=logical_replication' );"

psql -d zoomtivity_rep -c "SELECT pglogical.create_subscription(
        subscription_name := 'subscription',
        provider_dsn := 'host=107.150.34.138 port=5432 dbname=zoomtivity user=logical_replication',
        synchronize_structure := true,
        synchronize_data := true
);"

