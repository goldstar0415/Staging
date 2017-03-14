#!/usr/bin/env bash

composer run-script --no-dev post-install-cmd && \
/config/bootstrap.sh
