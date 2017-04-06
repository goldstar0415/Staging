#!/usr/bin/env bash

set -x
set -e

DOCKER_PROJECT=zoomtivity
TARGET_SERVICE=database
PROJECT_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BACKUPS_PATH=${PROJECT_ROOT}/postgres/backups
DUMP_FILE=${DOCKER_PROJECT}_`date +%Y-%m-%d"_"%H_%M_%S`
CONTAINER_ID=$(docker ps --filter="name=${DOCKER_PROJECT}_${TARGET_SERVICE}" -q -n 1)

docker exec ${CONTAINER_ID} \
    pg_dumpall -c -U postgres > ${BACKUPS_PATH}/${DUMP_FILE}.sql

tar -zcvf ${BACKUPS_PATH}/${DUMP_FILE}.tgz -C ${BACKUPS_PATH} ${DUMP_FILE}.sql
rm ${BACKUPS_PATH}/${DUMP_FILE}.sql
