#!/usr/bin/env bash

docker-compose -p zoomtivity exec backend php artisan migrate
