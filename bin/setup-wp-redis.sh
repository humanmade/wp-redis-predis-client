#!/usr/bin/env bash

WP_REDIS=/tmp/nathanielks-wp-redis
MYSQL_DB="${MYSQL_DB:-wordpress_test}"
MYSQL_USER="${MYSQL_USER:-root}"
MYSQL_PASS="${MYSQL_PASS:-}"
MYSQL_HOST="${MYSQL_HOST:-localhost}"

git clone -b make-redis-pluggable --single-branch --depth 1 https://github.com/nathanielks/wp-redis.git ${WP_REDIS}

cd "${WP_REDIS}" || exit
composer install
bash bin/install-wp-tests.sh "${MYSQL_DB}" "${MYSQL_USER}" "${MYSQL_PASS}" "${MYSQL_HOST}" latest
