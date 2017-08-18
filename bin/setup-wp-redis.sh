#!/usr/bin/env bash

WP_REDIS=/tmp/nathanielks-wp-redis

git clone -b make-redis-pluggable --single-branch --depth 1 git@github.com:nathanielks/wp-redis.git ${WP_REDIS}

cd "${WP_REDIS}" || exit
composer install
bash bin/install-wp-tests.sh wordpress_test root root localhost latest
