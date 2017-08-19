<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

$_wp_predis_dir = getenv( 'WP_PREDIS_DIR' );

require_once $_tests_dir . '/includes/functions.php';
require_once $_wp_predis_dir . '/vendor/autoload.php';
require_once $_wp_predis_dir . '/class-wp-predis-decorator.php';
require_once $_wp_predis_dir . '/functions.php';

tests_add_filter( 'wp_redis_check_client_dependencies_callback', 'WP_Predis\check_client_dependencies_callback' );
tests_add_filter( 'wp_redis_client_connection_callback', 'WP_Predis\client_connection_callback' );
tests_add_filter( 'wp_redis_setup_client_connection_callback', 'WP_Predis\setup_client_connection_callback' );
tests_add_filter( 'wp_redis_retry_exception_messages', 'WP_Predis\append_error_messages' );
