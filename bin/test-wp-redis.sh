#! /usr/bin/env bash
SCRIPT="$(realpath "$0")"
SCRIPTPATH="$(dirname "$SCRIPT")"
WP_PREDIS="${SCRIPTPATH}/.."
WP_REDIS=/tmp/wp-redis
WP_TESTS=/tmp/wordpress-tests-lib
PHPUNIT="${WP_PREDIS}/vendor/bin/phpunit"
BOOTSTRAP_FILENAME="bootstrap-wp-predis.php"
BOOTSTRAP_FILE="${WP_PREDIS}/tests/${BOOTSTRAP_FILENAME}"
REQUIRE_STATEMENT="require_once dirname( __FILE__ ) . '/${BOOTSTRAP_FILENAME}';"
TESTS_BOOTSTRAP="${WP_TESTS}/${BOOTSTRAP_FILENAME}"
TESTS_CONFIG="${WP_TESTS}/wp-tests-config.php"

# If wp-predis-bootstrap.php doesn't exist in the tests lib folder, copy it
# there
if [[ ! -f "${TESTS_BOOTSTRAP}" || -n "${FORCE_COPY}" ]]; then
  echo "Copying ${BOOTSTRAP_FILE} to ${TESTS_BOOTSTRAP}"
  cp "${BOOTSTRAP_FILE}" "${TESTS_BOOTSTRAP}"
else
  echo "${TESTS_BOOTSTRAP} exists, not copying in place."
fi

# if the wp-predis-bootstrap.php isn't required in wp-tests-config.php, append
# it to the file
if ! grep -Fxq "${REQUIRE_STATEMENT}" "${TESTS_CONFIG}"; then
  echo "Appending REQUIRE_STATEMENT to ${TESTS_CONFIG}"
  echo "${REQUIRE_STATEMENT}" | tee --append "${TESTS_CONFIG}"
else
  echo "REQUIRE_STATEMENT exists in ${TESTS_CONFIG}"
fi

# Let's test WP Redis with Predis as the adapter!
export WP_PREDIS_DIR="${WP_PREDIS}"
(cd "${WP_REDIS}" && eval "$PHPUNIT")
