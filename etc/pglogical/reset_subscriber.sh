#!/bin/bash

set -e

psql -d zoomtivity_rep -c "SELECT pglogical.drop_subscription('subscription');";
psql -d zoomtivity_rep -c "SELECT pglogical.drop_node('subscriber');"

